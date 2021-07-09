<?php
namespace pruning;

use tool\arrayUtil;

/**
 * 活四判断
 */
class preventLink4 extends base implements pruning {

    protected $priority = 80;
    
    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $point 检验落子位
     * @param int $color 落子方
     * @return bool 是否可落子
     */
    public function doCheck($map, $point, $color) {
        $links = $map->set($point->position(), $color)->doubleFreeLinkN($color, 4);
        foreach ($links as $link) {
            if (arrayUtil::inArray($point->position(), $link->getElements())) {
                return true;
            }
        }
        return null;
    }
}