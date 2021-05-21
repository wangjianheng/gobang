<?php
namespace pruning;

/**
 * 防止落点远离己方棋子
 */
class preventAway extends base implements pruning {

    //限制与己方棋子最远距离
    const FARTHEST_DISTANCE = 10;

    protected $priority = 50;
    
    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $point 检验落子位
     * @param int $color 落子方
     * @return bool 是否可落子
     */
    public function doCheck($map, $point, $color) {
        print_r($point);
        print_r($map);
        echo $color;die;
    }
}