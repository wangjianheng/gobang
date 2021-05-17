<?php
namespace tool;

use Illuminate\Support\Arr;

class chessboard {
    
    /**
     * 构建一个空棋盘
     * @return array 15 * 15空数组
     */
    public static function buildChessBoard() {
        static $allPositions;
        (! $allPositions) and $allPositions = array_fill(0, CHESSBOARD_SIZE,  array_fill(0, CHESSBOARD_SIZE, null));
        return $allPositions;
    }
    
    /**
     * 生成下一个可能的落点
     * @param array $map 当前棋子分布
     * @return array 可能落点的所有可能
     */
    public static function nextSteps($map) {
        $map = array_filter(arrayUtil::flatten($map));
        
        //全棋盘与map取diff 即可落子点
        $allPoint = arrayUtil::flatten(self::buildChessBoard());
        $chooise = array_diff_key($allPoint, $map);
        
        return array_map(function ($point) {
            return explode('.', $point);
        }, array_keys($chooise));  
    }
    
    /**
     * 取对手棋色
     * @param int $color 棋色
     */
    public static function negateColor($color) {
        $map = [
          STONE_BLACK => STONE_WHITE,
          STONE_WHITE => STONE_BLACK,
        ];

        return Arr::get($map, $color);
    }
    
    /**
     * 两个棋子是否相邻
     * @param array $p1
     * @param array $p2
     * @return bool
     */
    public static function isAdjoin($p1, $p2) {
        $distance = pow($p1[0] - $p2[0], 2) + pow($p1[1] - $p2[1], 2);
        return $distance > 0 && $distance <= 2;
    }
    
    /**
     * 计算两点之间斜率 只有三种 横:0 竖:false 斜线:1
     * @param array $p1
     * @param array $p2
     */
    public static function celGradient($p1, $p2) {
        $distanceX = $p1[0] - $p2[0];
        $distanceY = $p1[1] - $p2[1];
        return $distanceY === 0 ? false : ($distanceX / $distanceY);
    }
    
    /**
     * 判断坐标是否在棋盘内
     * @param array $position 位置
     * @return bool
     */
    public static function inChessboard($position) {
        //isset 即使存在值 但如果值为null 也会返回false 不可用
        $allPosition = self::buildChessBoard();
        return array_key_exists($position[0], $allPosition)
                &&
               array_key_exists($position[1], $allPosition[$position[0]]);
    }
    
    
}

