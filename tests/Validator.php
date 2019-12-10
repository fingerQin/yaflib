<?php
/**
 * 数据验证器工具类使用。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Validator;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

// [1] 手机号验证。
$mobile = '14812345678';
if (Validator::is_mobilephone($mobile)) {
    echo "手机号正确\n";
} else {
    echo "手机号错误\n";
}

// [2] 中文验证。
$username = '覃礼钧';
if (Validator::is_chinese($username)) {
    echo "是中文\n";
} else {
    echo "不是中文\n";
}

// [3] 组合验证。
// 支持的验证器规则有很多。暂时只列举部分。想了解更多，直接查看源文件。
$data = [
    'username' => '覃礼钧',
    'sex'      => '20',
    'mobile'   => '14812345678',
    'ip'       => '192.168.56.1',
    'money'    => '1000000.00',
    'email'    => '753814253@qq.com',
    'idcard'   => ''
];
$rules = [
    'username' => '姓名|require|chinese|len:2:10:1',
    'sex'      => '年龄|require|integer|number_between:0:120',
    'mobile'   => '手机号|require|mobilephone',
    'ip'       => 'IP地址|require|ip',
    'money'    => '身价|require|float',
    'email'    => '联系邮箱|require|email',
    'idcard'   => '身份证号|idcard' // 注意：当某个值希望在传了的时候才做验证就这样写。
];
Validator::valido($data, $rules); // 验证不通过会直接报错。