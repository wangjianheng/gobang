<?php
namespace carpet;
use carpet\chessMap;
use carpet\point;
use model\carpet;
use tool\chessboard;
use tool\arrayUtil;

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
            
            array_walk($whiteChooise, function (& $position) use ($map) {
                $position = app(point::class, ['position' => $position]);
                $position = $this->through(app(WHITE_PRUINGS), [
                    $map,
                    $position,
                    STONE_WHITE,
                ]);
            });
            
            print_r($whiteChooise);die;
        }
    }
    
    /**
     * 落点通过剪枝策略
     * @param string $pipes 实例数组
     * @param array $params 入参
     * @return bool
     */
    public function through($pipes, $params) {
        //按优先级排序
        usort($pipes, function ($pruning1, $pruning2) {
            return (int)$pruning2->getPriority() - (int)$pruning1->getPriority();
        });
        
        //通过管道
        return call_user_func_array(arrayUtil::buildPipeline($pipes, 'check', function ($dealRes, $pruning, & $passable) {
            list($res, $priority) = $dealRes->getCheckRespond();
            
            //判定为false打断并返回
            if ($res === false) {
                return true;
            }
            
            /**
             * 把入参的position替换一下(坐标肯定不会变)
             * 为了将上一个节点的信息带入到下一个节点(优先级, 判断结果等)
             */
            $passable[1] = $dealRes;
            return false;
        }), [$params]);
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
