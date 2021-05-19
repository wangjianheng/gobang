<?php
namespace pruning;

use carpet\point;
interface pruning {
    
    /**
     * 校验当前落子是否通过
     * @param array $params   入参,含以下
     *         carpet\chessMap 棋盘所有落子
     *         carpet\point    检验落子位
     *         int             落子方
     * @param callable $next  下一个校验节点
     * @return point
     */
    public function check($params, $next) : point;
}
