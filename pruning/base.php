<?php
namespace pruning;

use traits\resCache;
use carpet\point;
use tool\arrayUtil;

abstract class base {
    
    use resCache;
    
    //当前最高优先级
    protected static $highestPriority;

    /**
     * 优先级:用于排序以及逻辑判断
     * @var int $priority
     */
    protected $priority = 0;
    
    public function __construct() {
        static::$highestPriority = 0;
    }

    /**
     * 校验当前落子是否通过
     * @param array $map 棋盘所有落子
     * @param array $position 检验落子位
     * @param int $color 落子方
     * @return bool 是否可落子
     */
    public function check($map, $position, $color) : point {
        //没有权限校验 直接返回
        if (! $this->accessCheck(...$position->getCheckRespond())) {
            return $position;
        }
        
        $res = $this->doCheck($map, $position, $color);
        return $position->checkRespond($res, $this->priority);
    }
    
    /*
     * 当前节点是否需要校验
     * (1)若存在高优先级节点给出落子结论, 则后续所有棋子都不需要在校验
     *    比如定义己方落子即赢为最高优先级节点, 若该结点给出落子结论 
     *    则只统计该节点或同等级节点返回的落子点, 可直接忽略低优先级 即needCheck返回false
     * 
     * (2)若高优先级节点未给出结论, 则低优先级可以继续判断 同等级之间都有一票否决权,
     *    由于已经按照优先级排好序 如果其中一个节点返回false 即可定义结果为false 
     *    这点所有实现放在了构建管道的时候
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
    abstract public function doCheck($map, $position, $color);

    /**
     * 限制范围型会计算出一个范围
     * 如果存在范围, 则只过范围内的点 并一票通过
     * 如果不存在范围 则继续其他剪枝逻辑的判断 并取消一票通过
     * @param array $position 落子点
     * @param array $range 范围
     */
    protected function inRange($position, $range) {
        /**
         * 一票通过这直接修改就好
         * 下一个棋谱会被重新实例化 不用担心影响后面棋谱的判断
         */
        if (empty($range)) {
            $this->oneVotePass = false;
            return true;
        }
        
        return arrayUtil::inArray($position, $range);
    }
    
    /**
     * 获取优先级
     * @return int
     */
    public function getPriority() {
        return $this->priority;
    }
    
}