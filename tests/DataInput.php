<?php
/**
 * DataInput 工具类使用。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\DataInput;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

$data = [
    'orderid'  => 12345678,
    'money'    => 888.88,
    'ext'      => ['sex' => 1, 'birthday' => '1997-07-01']
];

// 功能是将三目运算与类型判断相结合。
$username = DataInput::getString($data, 'username', 'anonymous');
$orderId  = DataInput::getInt($data, 'orderid', 0);
$moeny    = DataInput::getFloat($data, 'money', 0.00);
$extInfo  = DataInput::getArray($data, 'ext', []);


var_dump($username, $orderId, $moeny, $extInfo);