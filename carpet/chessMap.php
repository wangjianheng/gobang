<?php
namespace carpet;

use model\carpet;
use tool\arrayUtil;
use tool\chessboard;
use \Illuminate\Support\Arr;

/* 
 * 棋谱
 */
class chessMap {
    
    //落子位置
    protected $stonePosion;
    
    //棋子分布
    protected $chessMap;
    
    //签名 用于棋谱对比
    protected $sign;

    protected $carpetModel;

    public function __construct($chessMap, $stonePosion = null) {
        $this->chessMap = $chessMap;
        $this->stonePosion = $stonePosion;
        $this->sign = sha1(json_encode($chessMap));
        
        $this->carpetModel = new carpet();
    }
    
    /**
     * 获取分布
     */
    public function getChessMap() {
        return $this->chessMap;
    }
   
    /**
     * 棋盘落库
     */
    public function save() {
        ksort($this->chessMap);
        $param = [
            'map'      => $this->chessMap,
            'pid'      => $this->root,
            'position' => $this->stonePosion,
        ];
        return $this->carpetModel->saveMap($param);
    }
    
    /*
     * 统计所有相连组合 
     * 同一棋谱 可能会调的比较频繁 加一个缓存  
     * @param int $length 长度
     * @param int $color 哪种棋色
     * @return array
     */
    protected function getLinks($length, $color) {
        static $cache = [], $sign = null;
        if ($sign !== $this->sign || ! isset($cache[$color])) {
            $chessMap = arrayUtil::flatten($this->chessMap);
            $chessmen = array_filter($chessMap, function ($chessColor) use ($color) {
                return $chessColor == $color;
            });
            
            $chessmenPosition = array_map(function ($position) {
                return explode('.', $position);
            }, array_keys($chessmen));
            $cache[$color] = $this->getAllLinks($chessmenPosition);
            $sign = $this->sign;
        }
        
        return array_filter($cache[$color], function ($link) use ($length) {
            return $link->length() == $length;
        });
    }
    
    /**
     * 获取所有相连组合(取最长)
     * @param array $chessmen 所有棋子位置
     */
    protected function getAllLinks($chessmen) {
        $chessmen = [[7, 7], [7, 8], [7, 9], [6, 6], [5, 5], [8, 8]];
        //每个点都看作是一条链
        $allLinks = collect($chessmen)
                ->map(function ($point) {
                    return new link($point);
                });
        
        $ret = [];
        do {
            $allLinks = $allLinks->map(function ($link) use (& $ret, $chessmen) {
                //标识是否连入过点
                $linked = 0;
                
                foreach ($chessmen as $point) {
                    $linked |= (int)$link->link($point);
                }
                
                /**
                 * 没有可链接的了就扔到结果里
                 * 并且设置为false 等待过滤
                 */
                if (! $linked) {
                    (! $link->inLinks($ret)) and array_push($ret, $link);
                    return false;
                }
                return $link;
            })
            ->filter();

        } while (! $allLinks->isEmpty());
        
        return $ret;
    }

    /**
     * 活三
     * @param int $color 棋色
     * @param bool strict 严格版:头尾延申两步亦可落子
     * @return array
     */
    public function doubleFreeLink3($color, $strict = false) {
        //所有长度为3的链接
        $allLinks = $this->getLinks(3, $color);
        
        //头活且尾活
        return array_filter($allLinks, function ($link) use ($strict, $color) {
            $prevAndNext = array_filter([$link->prev(), $link->next()]);
            if (count($prevAndNext) < 2) {
                return false;
            }
            
            //再延申一次
            if($strict) {
                $linkClone = clone $link;
                array_walk($prevAndNext, [$linkClone, 'link']);
                array_push($prevAndNext, $linkClone->prev(), $linkClone->next());
                $prevAndNext = array_filter($prevAndNext);
                if (count($prevAndNext) < 4) {
                    return false;
                }
            }
            
            //所有延申点均未落对方子(为空或落己方子都可以)
            $prevAndNextExists = array_map([$this, 'get'], $prevAndNext);
            return empty(array_filter($prevAndNextExists, function ($existColor) use ($color) {
                return $existColor && $existColor !== chessboard::negateColor($color);
            }));
            
        });
    }
    
    /**
     * 冲四
     * @param int $color 棋色
     * @return array
     */
    public function singleFreeLink4($color) {
        //所有长度为4的链接
        $allLinks = $this->getLinks(4, $color);
        
        //头活或尾活
        return array_filter($allLinks, function ($link) {
            $prevAndNext = array_filter([$link->prev(), $link->next()]);
            
            //头和尾的延申点都是棋盘外点
            if (empty($prevAndNext)) {
                return false;
            }
            
            //存在延申点还没有被落子
            $prevAndNextExists = array_map([$this, 'get'], $prevAndNext);
            return count($prevAndNext) > count(array_filter($prevAndNextExists));
        });
    }
    
    /**
     * 双活
     * @param int $color 棋色
     * @return array
     */
    public function crossPositionLink2($color) {
        
    }

        /**
     * 获取棋盘某一个位置的棋子颜色
     * @param array $position 位置
     */
    public function get($position) {
        return $this->chessMap[$position[0]][$position[1]] ?? null;
    }
    
    
    
}

