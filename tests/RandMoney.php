<?php
/**
 * 随机金额工具类使用(模拟微信红包随机金额)。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\RandMoney;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

$RandMoney = new RandMoney();
$result = $RandMoney->splitReward(100, 20);

print_r($result);

var_dump(array_sum($result));



