<?php
/**
 * IP 相关。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Excel;
use finger\Ip;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

// [1] 获取 IP。
$ip = Ip::ip();
echo $ip . "\n";

// [2] 查询 IP 是否在某个 IP 段内。
$ip      = '192.168.56.20';
$startIp = '192.168.56.1';
$endIp   = '192.168.56.255';
$status  = Ip::isRange($startIp, $endIp, $ip);
echo $status ? '在' : '不在';
echo "\n";