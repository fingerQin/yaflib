<?php
/**
 * 缓存使用示例。
 * @author fingerQin
 * @date 2019-12-05
 */

use finger\App;
use finger\Cache;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ],
    'redis' => [
        'default' => [
            'host'  => '127.0.0.1',
            'port'  => '6379',
            'auth'  => '',
            'index' => '1'
        ],
        'other' => [
            'host'  => '127.0.0.1',
            'port'  => '6379',
            'auth'  => '',
            'index' => '2'
        ]
    ]
];

(new App($config));

// [1] 设置缓存。默认使用 default 映射的配置。
Cache::set('username', 'fingerQin');

// [2] 读取缓存。
$username = Cache::get('username');
echo $username;
echo "\n";

// [3] 删除缓存。
Cache::delete($username);

// [4] 自增。
$val = 'incr';
Cache::incr($val);
echo Cache::get($val);
echo "\n";

// [5] 自减。
$decr = 'decr';
Cache::decr($decr);
echo Cache::get($decr);
echo "\n";

// [6] 获取真实的 Redis 对象。默认获取 default 指定的 Redis 配置。
$redis = Cache::getRedisClient();
$redis->set('test', 123456); // 这样可以直接使用 Redis 扩展提供的原生方法完成更多的功能。

// [7] 切换 Redis 配置。
$redis = Cache::getRedisClient('other'); // 指定配置项名称。
$redis->set('test', 123456); // 这样可以直接使用 Redis 扩展提供的原生方法完成更多的功能。
