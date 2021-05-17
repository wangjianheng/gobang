<?php
namespace model;

use \Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Arr;

class carpet extends Model {
    
    //结束:赢
    const END_W = 1;
    
    //结束:输
    const END_F = 2;
    
    //未结束
    const END_O = 3;
    
    //表名
    protected $table = 'carpet';
    
    public $timestamps = false;

    //表列
    protected $fillable = [
        'map', 'maphash', 'pid', 'step', 'position', 'end',
    ];
    
    /**
     * 保存棋谱
     * @param array $param 入参
     * @param int $end
     */
    public function saveMap($param, $end = self::END_O) {
        $default = [
            'end'     => $end,
            'step'    => count($param['map']) + 1,
            'map'     => json_encode($param['map']),
            'maphash' => sha1(json_encode($param['map'])),
            'position' => join('.', $param['position']),
        ];
        $this->fill(array_merge($param, $default))->save();
        return $this->getKey();
    }
    
    /**
     * 批量获取数据
     * @param array $where 筛选条件
     */
    public function getList($where = []) {
        $query = array_reduce($where, function($query, $where) {
            return $query->where(...$where);
        }, $this->newQuery());
        
        return $query
                ->select(['id', 'map', 'position'])
                ->get()
                ->map(function ($item) {
                    $item = $item->toArray();
                    $item['map'] = json_decode($item['map'], true);
                    Arr::set($item['map'], $item['position'], STONE_BLACK);
                    return $item;
                })
                ->all();
    }
    
}

