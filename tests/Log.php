<?php
/**
 * 日志。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;

require(__DIR__ . '/../vendor/autoload.php');

// 因为锁使用 Redis 的特性实现，所以我们需要设置 Redis 配置。
$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

// [1] 字符串日志。
App::log('我是字符串日志', 'debug', 'log');

// [2] 数组日志。
$log = ['username' => 'fingerQin', 'sex' => 'll '];
App::log($log, 'debug', 'log');