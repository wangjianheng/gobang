<?php
namespace pruning;

use traits\resCache;
use carpet\point;
use tool\arrayUtil;
use tool\chessboard;

abstract class base {
    
    use resCache;
    
    //当前最高优先级
    protected static $highestPriority;

    /**
     * 优先级:用于排序以及逻辑判断
     * @var int $priority
     */
    protected $priority = 0;
    
    protected $reverse = false;

    /**
     * @param int $reverse 颜色是否反转:分析自己还是分析对方
     * @param int $priorityAdjust 优先级调整
     */
    public function __construct($reverse = false, $priorityAdjust = 0) {
        static::$highestPriority = 0;
        $this->reverse = $reverse;
        $this->priority += $priorityAdjust;
    }

    /**
     * 校验当前落子是否通过
     * @param array $params   入参,含以下
     *         carpet\chessMap 棋盘所有落子
     *         carpet\point    检验落子位
     *         int             落子方
     * @param callable $next  下一个校验节点
     * @return point
     */
    public function check($params, $next) : point {
        list($map, $point, $color) = $params;
        //没有权限校验 直接返回
        if (! $this->accessCheck(...$point->getCheckRespond())) {
            return $point;
        }
        
        $res = $this->doCheck(clone $map, $point, $this->getColor($color));
        $point = $point->checkRespond($res, $this->priority);
        
        /*
         * 如果校验不通过直接返回false
         * 没有结果或校验通过 继续让后面的节点校验
         */
        return $res === false ? $point : $next([$map, $point, $color]);
    }
    
    /*
     * 当前节点是否需要校验
     * (1)若存在高优先级节点给出落子结论, 则后续所有棋子都不需要在校验
     *    比如定义己方落子即赢为最高优先级节点, 若该结点给出落子结论 
     *    则只统计该节点或同等级节点返回的落子点, 可直接忽略低优先级 即needCheck返回false
     * 
     * (2)若高优先级节点未给出结论, 则低优先级可以继续判断 同等级之间都有一票否决权,
     *    由于已经按照优先级排好序 如果其中一个节点返回false 即可定义结果为false 
     * 
     * @param bool $res 上一个节点的判断逻辑
     * @param int $priority 上一个节点的优先级
     * @return bool
     */
    protected function accessCheck($res, $priority) {
        if ($res) {
            static::$highestPriority = max(static::$highestPriority, $priority);
        }
        
        /**
         * 已有节点给出落子结论
         * 只有优先级大于等于该节点才有权限判断
         */
        if (static::$highestPriority) {
            return $this->priority >= static::$highestPriority;
        }
        
        return true;
    }

    /**
     * 执行校验
     * @return bool
     */
    abstract public function doCheck($map, $point, $color);
    
    /**
     * 获取优先级
     * @return int
     */
    public function getPriority() {
        return $this->priority;
    }
    
    /**
     * 获取分析棋色
     * @param int $color 落子颜色
     */
    public function getColor($color) {
        return $this->reverse ? chessboard::negateColor($color) : $color;  
    }
    
}