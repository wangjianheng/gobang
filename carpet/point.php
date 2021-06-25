<?php
namespace carpet;

class point {
    //横坐标
    protected $abscissa;
    
    //纵坐标
    protected $ordinate;
    
    //校验结果
    protected $checkRes = [null, null];


    public function __construct($position = [null, null]) {
        list($this->abscissa, $this->ordinate) = $position;
    }
    
    /**
     * 赋值校验结果
     * @param bool $res 校验结果
     * @param int $priority 校验类优先级
     */
    public function checkRespond($res = null, $priority = null) {
        $this->checkRes = [$res, $priority];
        return $this;
    }

    /**
     * 清空校验结果
     */
    public function clear() {
        $this->checkRes = [null, null];
        return $this;
    }
    
    /**
     * 获取校验结果
     */
    public function getCheckRespond() {
        return $this->checkRes;
    }
    
    /**
     * 获取坐标
     */
    public function position() {
        return [$this->abscissa, $this->ordinate];
    }

    /**
     * 获取与另一个点的距离
     * @param array $point 另一个点
     */
    public function distance($point) {
        $point instanceof point && $point = $point->position();
        $self = $this->position();
        return pow($self[0] - $point[0], 2) + pow($self[1] - $point[1], 2);
    }


    
    
}

