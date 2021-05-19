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


    
    
}

