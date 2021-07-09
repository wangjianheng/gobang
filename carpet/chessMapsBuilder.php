<?php
namespace carpet;
use model\carpet;
use tool\chessboard;
use tool\arrayUtil;
use \Illuminate\Support\Arr;

/**
 * 构建地毯谱, 用广度优先
 * 由于数据巨大, 借助数据库为队列 $point为指针依次向下遍历
 * 入队列过程中需要经过合理剪枝
 */
class chessMapsBuilder {
    const SELF_COLOR = STONE_BLACK;
    
    const FEATCH_STEP = 100;
    
    //当前指针
    protected $point = 500;
    
    protected $carpetModel;

    //黑棋先手走天元
    public function __construct() {
        $middle = intval(CHESSBOARD_SIZE / 2);
        //(new chessMap([]))->save(carpet::END_O, [[$middle, $middle]]);

        $this->carpetModel = new carpet();
    }

    /**
     * 开始构建
     */
    public function build() {
        //非对称棋谱集合
        $uniqueBySymmetry = [];

        foreach ($this->progressMaps() as $map) {
            /**
             * 下一手白棋出, 统计白棋所有可落子点
             * 经过筛选统计剩下的落子点
             */
            $whiteChooise = chessboard::nextSteps($map->getChessMap());
            $whiteChooisebak = $whiteChooise;
            list($whiteChooise, $whitePriority) = $this->chooiseFilter($map, $whiteChooise, STONE_WHITE);

            if (empty($whiteChooise)) {
                print_r($map);
                print_r($whiteChooisebak);
                die;
            }
            
            /**
             * 下一手黑棋出, 统计所有黑棋可能落点并落库
             */
            foreach ($whiteChooise as $point) {
                $mapWithWhite = (clone $map)->set($point, STONE_WHITE);

                //此时白棋已经赢了
                if ($whitePriority > WIN_PRIORITY) {
                    $mapWithWhite->save(carpet::END_F);
                    continue;
                }

                $blackChooise = chessboard::nextSteps($mapWithWhite->getChessMap());
                list($blackChooise, $blackPriority) = $this->chooiseFilter($mapWithWhite, $blackChooise, STONE_BLACK);
                $win = $blackPriority > WIN_PRIORITY ? carpet::END_W : carpet::END_O;

                //黑棋选点落库
                //$mapWithWhite->save($win, $blackChooise);
            }
        }
    }

    /**
     * 是否存在对称的棋谱(这个会给后期计算也带来麻烦 暂时先不做了)
     * @param array $map 棋谱
     * @param array $uniqueBySymmetry 棋谱的集合
     * @return boolean
     */
    protected function existsSymmetry($map, & $uniqueBySymmetry) {
        /**
         * 数量越多计算量越大 且出现对称的概率越小
         * 只判断前几手就好了
         */
        if(count($map->getChessMap()) > 6) {
            return false;
        }
    }
    
    /**
     * 对可落点进行过滤
     * @param chessMap $map 棋谱
     * @param array $chooise 所有可落点
     * @param int $color 落子方
     * @return array
     */
    protected function chooiseFilter($map, $chooise, $color) {
        $policyMap = [
            STONE_WHITE => WHITE_PRUINGS,
            STONE_BLACK => BLACK_PRUINGS,
        ];
        $policy = $policyMap[$color];
        
        foreach ($chooise as & $point) {
            $point = app(point::class, ['position' => $point]);
            $point = $this->through(app($policy), [$map, $point, $color]);
        }
        
        //选优先级最高的点
        $priority = [];
        $chooiseFilter = [];
        foreach ($chooise as $point) {
            list($res, $priority) = $point->getCheckRespond();
            $res && $chooiseFilter[$priority][] = $point->position();
        }
        ksort($chooiseFilter);
        return [array_pop($chooiseFilter), $priority];
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
        return call_user_func_array(arrayUtil::buildPipeline($pipes, 'check', function ($res) {
            $point = $res[1];
            list($res, $priority) = $point->getCheckRespond();
            return is_null($res) ? $point->checkRespond(true, 0) : $point;
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
                foreach ($map['position'] as $position) {
                    $currentMap = $map['map'];
                    Arr::set($currentMap, $position, STONE_BLACK);
                    yield new chessMap($currentMap, $map['id']);
                }
            }
            $this->point = $map['id'];
        } while ($mapList);
    }
    
}
