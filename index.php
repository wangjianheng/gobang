<?php
require 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use carpet\chessMapsBuilder;

define('STONE_BLACK', 1);
define('STONE_WHITE', 2);
define('WHITE_PRUINGS', 'white_pruings');
define('BLACK_PRUINGS', 'black_pruings');
define('CHESSBOARD_SIZE', 15);

/**
 * 自定义类的加载
 */
spl_autoload_register(function($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (! file_exists($file)) {
        exit("class $class not fonund");
    }
    require_once $file;
});

//调试
$degug = false;

//容器
$app = new Container();

//配置加载
$env = array_merge(parse_ini_file('.env', true), [
    'database.default' => null,
]);
$app->instance('config', $env);

//数据源
$resolver = $app->make(DatabaseManager::class, ['app' => $app]);
Model::setConnectionResolver($resolver);

//白棋剪枝策略
$whitePruings = [
    //阻止冲四 & 活三
    pruning\prevent::class,
    ];
$app->instance(WHITE_PRUINGS, array_map('app', $whitePruings));

//地毯谱
$app->make(chessMapsBuilder::class)->build();


