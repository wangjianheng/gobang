<?php
namespace pruning;

use tool\chessboard;

/**
 * 阻止冲四 & 活三
 * 限制范围型:$position必须落在计算出范围内
 */
class prevent implements pruning{
    
    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $position 检验落子位
     * @param int $color 落子方
     * @return bool 是否可落子
     */
    public function check($map, $position, $color) {
        $fatalLinks = array_merge(
            //活3
            $map->doubleFreeLink3(chessboard::negateColor($color), true),
            
            //冲4
            $map->singleFreeLink4(chessboard::negateColor($color))
        );
        
        //这些连线必须截住
        print_r($fatalLinks);
        die;
        
        
    }
    
    /**
     * 一票通过权 即不需考虑其他策略的决定, 直接通过
     * 目前需要先防守对手, 直接作废后面的推荐点位
     * @return bool
     */   
    public function oneVotePass() {
        return true;
    }
    
    /**
     * 一票否决权 返回true会直接排在最前面
     * 主要用于落子可赢的情况 可直接排除其他落点
     * @return bool
     */
    public function oneVoteVeto() {
        return false;
    }
    
    
    
}