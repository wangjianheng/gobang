<?php
namespace traits;

/**
 * 为方法调用设置缓存
 * 解决问题:同一方法, 相同参数的多次调用
 * 解决方法:判断参数相同(sign标识), 第一次之后均走缓存
 * 注意:适用于同一sign连续多次调用的情况(不同类不影响), 交替调用没有缓存效果
 */
trait resCache {
    
    protected static $cache = null;
    
    /**
     * 方法缓存层隔离
     * @param type $sign 用于判断是否相同
     * @param type $callable 回调函数
     */
    public static function cacheGet($sign, $callable = null) {
        list($cacheSign, $cacheRes) = static::$cache[static::class] ?? [null, null];
        
        if ($sign == $cacheSign) {
            return $cacheRes;
        }
        
        static::$cache[static::class] = [$sign, $res = $callable()];
        return $res;
    }
    
}

