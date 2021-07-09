<?php
namespace carpet;

use model\carpet;
use tool\arrayUtil;
use tool\chessboard;
use traits\resCache;

/* 
 * 棋谱
 */
class chessMap {
    
    use resCache;
    
    //棋子分布
    protected $chessMap;
    
    //签名 用于棋谱对比
    protected $sign;

    protected $root;

    public function __construct($chessMap, $root = 0) {
        $this->chessMap = $chessMap;
        $this->sign = sha1(json_encode($chessMap));
        $this->root = $root;
    }
    
    /**
     * 获取分布
     * @param int $color 获取哪种颜色的棋子 null全要
     * @param boolean $flatten 是否铺平
     * @return array
     */
    public function getChessMap($color = null, $flatten = false) {
        if (is_null($color)) {
            return $this->chessMap;
        }

        return self::cacheGet($this->sign . (int)$flatten, function () use ($color, $flatten) {
            $ret = array_filter($this->chessMap, function ($item) use ($color) {
                return current($item) == $color;
            });
            return $flatten ? arrayUtil::flatten($ret) : $ret;
        });
    }
   
    /**
     * 棋盘落库
     * @param array $stonePosion 落点位置
     * @return int
     */
    public function save($win, $stonePosions = []) {
        if (empty($stonePosions)) {
            return ;
        }

        ksort($this->chessMap);
        $param = [
            'map'      => $this->chessMap,
            'pid'      => $this->root,
            'position' => $stonePosions,
        ];
        return (new carpet())->saveMap($param, $win);
    }
    
    /*
     * 统计所有相连组合 
     * 同一棋谱 可能会调的比较频繁 加一个缓存  
     * @param int $color 棋色
     * @param int $length 长度
     * @return array
     */
    public function getLinks($color, $length = null) {
        $links = self::cacheGet($this->sign, function () use ($color) {
            $chessMap = arrayUtil::flatten($this->chessMap);
            $chessmen = array_filter($chessMap, function ($chessColor) use ($color) {
                return $chessColor == $color;
            });
            
            $chessmenPosition = array_map(function ($position) {
                return explode('.', $position);
            }, array_keys($chessmen));
            return $this->getAllLinks($chessmenPosition);
        });
        
        if (is_null($length)) {
            return $links;
        }
        
        return array_filter($links, function ($link) use ($length) {
            return $link->length() == $length;
        });
    }
    
    /**
     * 获取所有相连组合(取最长)
     * @param array $chessmen 所有棋子位置
     */
    protected function getAllLinks($chessmen) {
        //每个点都看作是一条链
        $allLinks = collect($chessmen)
                ->map(function ($point) {
                    return new link($point);
                });
        
        $ret = [];
        do {
            $allLinks = $allLinks->map(function ($link) use (& $ret, $chessmen) {
                //标识是否连入过点
                $linked = false;
                
                foreach ($chessmen as $point) {
                    $linked = $linked || $link->link($point);
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
     * 长度为$len并且头尾皆活头尾
     * @param int $color 棋色
     * @return array
     */
    public function doubleFreeLinkN($color, $len) {
        //所有长度为$len的链接
        $allLinks = $this->getLinks($color, $len);
        
        //头活且尾活
        return array_filter($allLinks, function ($link) use ($color) {
            $prevAndNext = array_filter([$link->prev(), $link->next()]);
            if (count($prevAndNext) < 2) {
                return false;
            }
            
            //所有延申点均未落对方子
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
        $allLinks = $this->getLinks($color, 4);
        
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
    
    public function set($position, $color) {
        if ($this->get($position)) {
            return false;
        }
        
        $this->chessMap[$position[0]][$position[1]] = $color;
        $this->sign = sha1(json_encode($this->chessMap));
        return $this;
    }

    /**
     * 获取签名
     */
    public function getSign() {
        return $this->sign;
    }
    
    
    
}

