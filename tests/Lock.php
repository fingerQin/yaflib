<?php
/**
 * 锁使用示例。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Lock;

require(__DIR__ . '/../vendor/autoload.php');

// 因为锁使用 Redis 的特性实现，所以我们需要设置 Redis 配置。
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
        ]
    ]
];

(new App($config));

// [1] 第二个参数设置为 0 代表取锁失败，不进行等待立即返回取锁结果。第三个值代表取锁成功立即加锁时长。
$key = 'user-money-key';
$redis = Lock::lock($key, 0, 10);
if ($redis) {
    echo "取到锁\n";
} else {
    echo "未取到锁\n";
}
// 业务处理完之后，记得主动释放锁。如果忘记主动释放了。会直至 10 秒后才会主动释放。
// 可以注释掉这行代码查看锁定效果。
// Lock::release($key);

// [2] 阻塞锁的读取时间。如果 3 秒内尝试去取锁都未获取，则返回失败结果。
$redis = Lock::lock($key, 3, 10);
if ($redis) {
    echo "取到锁\n";
} else {
    echo "未取到锁\n";
}
Lock::lock($key);