<?php
require 'vendor/autoload.php';

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Connectors\ConnectionFactory;
use carpet\chessMapsBuilder;

define('STONE_BLACK', 1);
define('STONE_WHITE', 2);
define('WHITE_PRUINGS', 'white_pruings');
define('BLACK_PRUINGS', 'black_pruings');
define('CHESSBOARD_SIZE', 15);

//调试
$degug = false;

//配置加载
$env = array_merge(parse_ini_file('.env', true), [
    'database.default' => null,
]);
app()->instance('config', $env);

//数据源
$connectionFactory = app(ConnectionFactory::class, ['container' => app()]);
$resolver = app(DatabaseManager::class, ['app' => app(), 'factory' => $connectionFactory]);

Model::setConnectionResolver($resolver);

//白棋剪枝策略
$whitePruings = [
    pruning\preventLink5::class => ['reverse' => false, 'priorityAdjust' => 1],
    pruning\preventLink5::class => ['reverse' => true,  'priorityAdjust' => 0],
    pruning\preventLink4::class => ['reverse' => true,  'priorityAdjust' => 1],
    pruning\preventLink4::class => ['reverse' => true,  'priorityAdjust' => 0],
];
app()->instance(WHITE_PRUINGS, array_map('app', array_keys($whitePruings), array_values($whitePruings)));

//黑棋剪枝策略
$bluckPruings = [
    pruning\preventLink5::class => ['reverse' => false, 'priorityAdjust' => 1],
    pruning\preventLink5::class => ['reverse' => true,  'priorityAdjust' => 0],
    pruning\preventLink4::class => ['reverse' => true,  'priorityAdjust' => 1],
    pruning\preventLink4::class => ['reverse' => true,  'priorityAdjust' => 0],
    pruning\preventAway::class  => ['reverse' => false,  'priorityAdjust' => 0]
];
app()->instance(BLACK_PRUINGS, array_map('app', array_keys($bluckPruings), array_values($bluckPruings)));


//地毯谱
app(chessMapsBuilder::class)->build();


