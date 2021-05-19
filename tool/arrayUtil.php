<?php
namespace tool;

use Illuminate\Support\Arr;

class arrayUtil {
    
    /**
     * 将数组铺平
     * @param array $array 铺平的数组
     * @param int $deep 铺平的维度
     */
    public static function flatten($array, $deep = INF, $root = '') {
        $single = [];
        foreach ($array as $k => $v) {
            if ($deep > 1 && is_array($v)) {
                $single += self::flatten($v, $deep - 1, $k);
            } else {
                $root !== '' && $k = "$root.$k";
                $single[$k] = $v;
            }
        }
        return $single;
    }
    
    /**
     * 根据多个元素排序
     * @param array $array 数组
     * @param array $fields 元素
     * @param bool $aes 正序
     */
    public static function sortByFields(& $array, $fields, $aes = true) {
        $aes = $aes ? 1 : -1;
        usort($array, function ($item1, $item2) use ($aes, $fields) {
            return self::compareByFields($aes, $item2, $fields) * $aes;
        });
    }
    
    /**
     * 根据多个元素对比
     * @param mixed $item1 元素1
     * @param mixed $item2 元素2
     * @param array $fields 对比列
     * @return bool 对比结果
     */
    public static function compareByFields($item1, $item2, $fields) {
        if (empty($fields)) {
            return 0;
        }
        
        $currentField = array_shift($fields);
        $item1Val = self::get($item1, $currentField);
        $item2Val = self::get($item2, $currentField);
        
        if ($item1Val == $item2Val) {
            return self::compareByFields($item1, $item2, $fields);
        }
        
        return $item2Val - $item1Val;
    }
    
    /**
     * 获取数据元素或对象属性
     * @param mixed $value 元素
     * @param string $key 键值
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($value, $key, $default = null) {
        if (is_array($value)) {
            return Arr::get($value, $key, $default);
        }
        
        if (is_object($value)) {
            return $value->$key ?? $default;
        }
        
        return $default;
    }
    
    /**
     * 支持元素为数组的判断
     * @param mixed $item
     * @param array $array
     * @return bool
     */
    public static function inArray($item, $array) {
        $array = array_filter($array, function ($val) use ($item) {
            return self::compareArray($val, $item);
        });
        return ! empty($array);
    }
    
    /**
     * 对比两个数组是否相等
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    public static function compareArray($array1, $array2) {
        $keys = array_intersect(array_keys($array1), array_keys($array2));
        if (count($keys) !== count($array1) || count($keys) !== count($array2)) {
            return false;
        }
        
        foreach ($keys as $k) {
            if (is_array($array1[$k]) && is_array($array2[$k])) {
                $same = self::compareArray($array1[$k], $array2[$k]);
            } else if (! is_array ($array1[$k]) && ! is_array($array2[$k])) {
                $same = $array1[$k] == $array2[$k];
            } else {
                $same = false;
            }
            
            if (! $same) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 构建管道
     * @param array $pipes 实例数组
     * @param string $func 调用的方法
     * @param callable $then 最后通过的方法
     * @return callable
     */
    public static function buildPipeline($pipes, $func, $then = 'value') {
        //将剪枝的类转为方法
        $pipes = array_map(function ($pruning) use ($func) {
            return function ($passable, $next) use ($pruning, $func) {
                return call_user_func_array([$pruning, $func], [$passable, $next]);
            };
        }, $pipes);
        
        //构建管道
        return array_reduce(array_reverse($pipes), function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                return $pipe($passable, $stack);
            };
        }, $then);
    }
    
    /**
     * 笛卡尔乘积
     * @param array $arrays
     * @return array
     */
    public static function arrayCross(...$arrays) {
        if (count($arrays) < 2) {
            return current($arrays);
        }
        
        $mixed = array_map(function($item) {
            return [$item];
        }, array_shift($arrays));
        
        while ($arrays) {
            $tmp = [];
            foreach (array_shift($arrays) as $v1) {
                foreach ($mixed as $v2) {
                    array_push($tmp, array_merge($v2, [$v1]));
                }
            }
            $mixed = $tmp;
        }
        
        return $mixed;
    }
    
    
}
