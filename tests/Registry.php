<?php
/**
 * 全局对象注册器。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Registry;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

// [1] 准备数据。
$data = ['username' => 'fingerQin'];

// [2] 注册数据。
Registry::set('userinfo', $data);

// [3] 判断数据是否存在。
$status = Registry::has('userinfo');
echo $status ? '存在' : '不存在';
echo "\n";

// [4] 获取数据。
$result = Registry::get('userinfo');

var_dump($result);

Registry::del('userinfo');

var_dump(Registry::get('userinfo'));