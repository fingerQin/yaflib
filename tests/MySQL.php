<?php
/**
 * 数据库使用示例。
 * @author fingerQin
 * @date 2019-12-05
 */

use finger\App;
use finger\Database\Db;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
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
    ]
];

(new App($config));

/**
CREATE TABLE `tb_user` (
  `userid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `salt` char(6) NOT NULL COMMENT '密码盐',
  `pwd` char(32) NOT NULL COMMENT '密码',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `uk_t` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表(示例)';
*/

// ------ 以下只是包装了一下 PDO -------
// [1] 读取一条记录。返回一维数组。
$data = Db::one('SELECT * FROM tb_user');
print_r($data);

// [2] 读取所有记录。返回二维数组。
$data = Db::all('SELECT * FROM tb_user');
print_r($data);

// [3] 计算总数。
$count = Db::count('SELECT * FROM tb_user');
echo $count;
echo "\n";

// [4] 添加数据。
$sql = 'INSERT INTO tb_user (mobile,salt,pwd) VALUES(:mobile,:salt,:pwd)';
$params = [
    ':mobile' => time(),
    ':salt'   => 'xxxxxx',
    ':pwd'    => md5('12345678xxxxxx')
];
$insertId = Db::execute($sql, $params);
var_dump($insertId);
