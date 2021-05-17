<?php
namespace pruning;

interface pruning {
    
    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $position 检验落子位
     * @param int $color 落子方
     * @return bool
     */
    public function check($map, $position, $color);
    
    /**
     * 一票通过权 即不需考虑其他策略的决定, 直接通过
     * @return bool
     */    
    public function oneVotePass();
}
