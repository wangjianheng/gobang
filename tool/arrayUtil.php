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
    
    
}
