<?php
namespace carpet;
use carpet\chessMap;
use model\carpet;
use tool\chessboard;

/**
 * 构建地毯谱, 用广度优先
 * 由于数据巨大, 借助数据库为队列 $point为指针依次向下遍历
 * 入队列过程中需要经过合理剪枝
 */
class chessMapsBuilder {
    const SELF_COLOR = STONE_BLACK;
    
    const FEATCH_STEP = 100;
    
    //当前指针
    protected $point = 0;
    
    protected $carpetModel;

    //黑棋先手走天元
    public function __construct() {
        $middle = intval(CHESSBOARD_SIZE / 2);
        //$this->point = (new chessMap([], [$middle, $middle]))->save();
        
        $this->carpetModel = new carpet();
    }

    /**
     * 开始构建
     */
    public function build() {
        foreach ($this->progressMaps() as $map) {
            /**
             * 下一手白棋出, 统计白棋所有可落子点
             * 经过筛选统计剩下的落子点
             */
            $whiteChooise = chessboard::nextSteps($map->getChessMap());
            
            $whiteChooise = array_filter($whiteChooise, function ($position) use ($map) {
                return $this->through(app(WHITE_PRUINGS), [
                    $map,
                    $position,
                    STONE_WHITE,
                ]);
            });
        }
    }
    
    /**
     * 落点通过剪枝策略
     * @param string $pipe 队列
     * @param array $params 入参
     * @return bool
     */
    public function through($pipes, $params) {
        //排序 优先级:一票否决>一票通过>其他
        usort($pipes, function ($pruning1, $pruning2) {
            $compore = (int)$pruning2->oneVoteVeto() - (int)$pruning1->oneVoteVeto();
            
            if ($compore !== 0) {
                return $compore;
            }
            
            return (int)$pruning2->oneVotePass() - (int)$pruning1->oneVotePass();
        });
        
        //通过管道
        return call_user_func_array($this->buildPipeline($pipes), [$params]);
    }
    
    /**
     * 构建管道
     * @param array $pipes
     * @return callable
     */
    protected function buildPipeline($pipes) {
        //将剪枝的类转为方法
        $pipes = array_map(function ($pruning) {
            return function ($passable, $next) use ($pruning) {
                $checkRes = call_user_func_array([$pruning, 'check'], $passable);
                
                //一票通过
                if ($checkRes && $pruning->oneVotePass()) {
                    return true;
                }
                
                //继续下面的判断
                return $next($passable);
            };
        }, $pipes);
        
        //构建管道
        return array_reduce(array_reverse($pipes), function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                return $pipe($passable, $stack);
            };
        });
        
        
    }

    /**
     * 通过数据库实现出队列
     * @return chessMap
     */
    protected function progressMaps() {        
        do {
            $where = [
                ['id', '>', $this->point],
                ['id', '<=', $this->point + self::FEATCH_STEP],
                ['end', '=', carpet::END_O],
            ];
            $mapList = $this->carpetModel->getList($where);
            
            foreach ($mapList as $map) {
                yield new chessMap($map['map'], null);
            }
            $this->point = $map['id'];
        } while ($mapList);
    }
    
}
