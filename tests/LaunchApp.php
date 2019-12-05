<?php
/**
 * 启动库入口。
 * @author fingerQin
 * @date 2019-12-04
 */

use finger\App;
use finger\Database\Db;
use finger\Utils\YCache;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => false,
        'key'       => 'e7efda40b1c94805070cd9bf9638ae27',
        'root_path' => __DIR__
    ],
    'upload' => [
        'driver'   => 'oss',            // 上传驱动设定：oss - 阿里云 OSS、local - 本地上传
        'save_dir' => '',               // 保存路径。local 本地上传才设置。
        'oss'      => [
            'access_key'    => '',      // OSS KEY。
            'access_secret' => '',      // OSS 密钥。
            'endpoint'      => '',      // OSS endpoint
            'bucket'        => ''       // OSS bucket
        ]
    ],
    'mysql' => [
        'default' => [
            'host'     => '127.0.0.1',
            'port'     => '3306',
            'user'     => 'admin',
            'pwd'      => '12345678',
            'dbname'   => 'test',
            'charset'  => 'UTF8',
            'pconnect' => true
        ],
        'pay' => [
            'host'     => '127.0.0.1',
            'port'     => '3306',
            'user'     => 'admin',
            'pwd'      => '12345678',
            'dbname'   => 'yafdb',
            'charset'  => 'UTF8',
            'pconnect' => true
        ],
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
        ],
    ]
];

(new App($config));

$config = App::getConfig('upload');

// $redis = YCache::getRedisClient('other');
// $redis->set('xxxx', '1234567');

$data = Db::one('SELECT * FROM tb_user');
print_r($data);