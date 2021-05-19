<?php
namespace pruning;

use tool\chessboard;
use tool\arrayUtil;

/**
 * 阻止对方的落子成活四
 */
class preventLink4 extends base implements pruning {

    protected $priority = 2;
    
    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $position 检验落子位
     * @param int $color 落子方
     * @return bool 是否可落子
     */
    public function doCheck($map, $position, $color) {
        //活3 + 落子 有可能构成活4
        $dubleFreeLink3 = $map->doubleFreeLinkN(chessboard::negateColor($color), 3);

        //连接落子
        $link4From3 = array_map(function ($link) use ($position) {
            return $link->link($position->position());
        }, $dubleFreeLink3);

        /**
         * 活2 + 落子 + 活1 有可能构成活4
         * (并没有活1一说 一个元素是不能确定方向的)
         */
        $dubleFreeLink2 = $map->doubleFreeLinkN(chessboard::negateColor($color), 2);
        
        //连接落子
        $link3From2 = array_map(function ($link) use ($position) {
            return $link->link($position->position());
        }, $dubleFreeLink2);
        
        //如果可以连接上, 那么尝试连接活1
        $dubleFreeLink1 = $map->getLinks(chessboard::negateColor($color), 1);
        if ($link3From2 = array_filter($link3From2)) {
            
        }
        
        
    }    
}