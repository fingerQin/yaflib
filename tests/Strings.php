<?php
/**
 * 字符串工具类使用。
 * -- 目前关于字符串的操作功能较少。后续会增长更多。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Strings;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));


// [1] 脱敏处理。
$mobile = '14812345678';
$mobile = Strings::asterisk($mobile, 3, 5);
echo $mobile . "\n";

// [2] 脱敏处理2。
$username = '覃礼钧';
$username = Strings::asterisk($username, 1, 10);
echo $username . "\n";

// [3] 随机字符串。
$rand = Strings::randomstr(6);
echo $rand . "\n";

// [4] 指定随机的字符串。
$rand = Strings::random(5, '1234567890');
echo $rand . "\n";