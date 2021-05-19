<?php
namespace pruning;

use carpet\point;
interface pruning {
    
    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $position 检验落子位
     * @param int $color 落子方
     * @return point
     */
    public function check($map, $position, $color) : point;
}
