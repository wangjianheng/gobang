<?php
namespace carpet;
use \tool\chessboard;
use Illuminate\Support\Arr;

/**
 * 相连棋子
 */
class link {
    
    //头
    protected $linkHead;
    
    //尾
    protected $linkEnd;
     
    //斜率 只有三种 横:0 竖:false 斜线:1
    protected $gradient = null;
    
    //元素
    protected $elements = [];
    
    public function __construct($point) {
        $this->linkEnd = $this->linkHead = $point;
        array_push($this->elements, $point); 
    }

    /**
     * 连入新的点
     * @param array $point 点
     * @return bool 连入成功true 不可连入false
     */
    public function link($point) {
        if (in_array($point, $this->elements)) {
            return false;
        }
        
        if (is_null($this->gradient)) {
            return $this->linkOnePoint($point);
        }
        
        //尝试从头连入
        if (chessboard::isAdjoin($this->linkHead, $point)) {
            if ($this->gradient === chessboard::celGradient($point, $this->linkHead)) {
                array_unshift($this->elements, $point);
                $this->linkHead = $point;
                return true;
            }
        }
        
        //尝试从尾连入
        if (chessboard::isAdjoin($this->linkEnd, $point)) {
            if ($this->gradient === chessboard::celGradient($point, $this->linkEnd)) {
                array_push($this->elements, $point);
                $this->linkEnd = $point;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 当目前只有一个元素的时候用这个方法
     * 只有一个元素 挨着就可以连 并且需要确定下斜率
     * @param array $point
     * @return bool 连入成功true 不可连入false
     */
    protected function linkOnePoint($point) {
        //头和尾是同一个点 用哪个都可以
        if (! chessboard::isAdjoin($this->linkHead, $point)) {
            return false;
        }
        
        array_push($this->elements, $point);
        $this->linkEnd = $point;
        $this->gradient = chessboard::celGradient($this->linkEnd, $this->linkHead);
        return true;
    }
    
    /**
     * 判断当前链接是否存在链接组中
     * @param array $links 存在链接组
     * @return bool
     */
    public function inLinks($links) {
        foreach ($links as $link) {
            if ($this->isSame($link)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 判断两个连接是否相同(存在交点且斜率相同)
     */
    public function isSame(link $link) {
        $samePoint = array_uintersect($link->getElements(), $this->elements, function ($p1, $p2) {
            return $p1[0] == $p2[0] && $p1[1] == $p2[1] ? 0 : -1;
        });
        
        return $samePoint
                &&
               $link->getGradient() === $this->getGradient();
    }
    
    /**
     * 头方向延申坐标
     * @return array
     */
    public function prev($reverse = false) {
        $elements = $reverse ? array_reverse($this->elements) : $this->elements;
        if (! $second = Arr::get($elements, 1)) {
            return false;
        }
        
        $first = $elements[0];
        $prev = [
            2 * $first[0] - $second[0],
            2 * $first[1] - $second[1],
        ];
        
        return chessboard::inChessboard($prev) ? $prev : false;
    }
    
    /**
     * 尾方向延申坐标
     * @return types
     */
    public function next() {
        return $this->prev(true);
    }
    
    

    public function getElements() {
        return $this->elements;
    }
    
    public function getGradient() {
        return $this->gradient;
    }
    
    public function length() {
        return count($this->elements);
    }
    
    
    
}

