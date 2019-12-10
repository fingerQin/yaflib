<?php
/**
 * Url 相关。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Url;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

// [1] 获取当前 URL。
$url = Url::getUrl();
echo "当前 Url:{$url}\n";

// [2] 获取当前域名。
$domainName = Url::getDomainName();
echo "域名：{$domainName}\n";