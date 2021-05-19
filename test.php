<?php
require 'vendor/autoload.php';

use Illuminate\Container\Container;
class test {
    public function __construct()
    {
        echo 123;
    }
}


$a = new Container();

$a->make('test');