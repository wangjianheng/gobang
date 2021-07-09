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
        $minePoints = $map->getChessMap($color, true);

        //白棋第一个落点 可以围绕着黑棋下
        if (empty($minePoints)) {
            $middle = intval(CHESSBOARD_SIZE / 2);
            $minePoints = [
                "{$middle}.{$middle}" => true,
            ];
        }

        $minDistance = INF;
        foreach (array_keys($minePoints) as $myPoint) {
            $distance = $point->distance(explode('.', $myPoint));
            $minDistance = min($minDistance, $distance);
        }

        //黑棋第二个子最好挨着第一个子
        $distanceLimitStretch = 0;
        if ($color == STONE_BLACK && count($minePoints) == 1) {
            $distanceLimitStretch = -8;
        }

        return $minDistance <= self::FARTHEST_DISTANCE + $distanceLimitStretch;
    }
}