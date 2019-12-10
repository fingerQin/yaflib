<?php
/**
 * Crypt 使用示例。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Crypt;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'secretKey'  => 'secret_pwd',
    'privateKey' => 'MIICXQIBAAKBgQCoudS1qyGXy1og8ELFKH7KV8uio0WIR4iRQYUXxkVR4VRMBEmlKtgaPM8EKS8UwcS4K+C4N26g0GjZRDgPVVNcKgdVgUpNMpkFcaqkHAEGdnxiAao+wHc+pFEiibPjt49Us8v6NNt9SqpZngyi2BnPN6V8shZj8H2b0URaTDbsdwIDAQABAoGAGvPcg8y7iAkG874NnHsUjfQqZmeYguWSbkm+HgchKaxKD/6bWRQYA1D1bN+7UqCFcTopIqRQOsYwCYz5O5HQx+S3oNpFo8DLaYgiJznJIUoUpJ3n0GhFgaVxwshZgH67Cno6SfcYdzh5LUVQUQgPuGS3bQi8sfRW++Z0blMC/fECQQDcTuFIavb03psr8tGfionsinSC5ES9hB3uIRkwrWbCT+7BkfhAq+miP7d6hEDbSkksDSFwzrR42v1tcejM4YPpAkEAxA+dkyp00kbqOOmNFvL5wh2Vf9a4KDfqn1znCYuwLoD5+p6m7gmeJlcsxrG5fwFZFvhj28gYsDioSGCk0FCRXwJASja0Q03tJS37/cENhn4H2kwF1bYJxgHEh1xU/QXc0OZWWpTOmRKQYJywNTBqHLUYkyNVOYgYWYIDzyOJNcNeSQJBAKLfQgzeaTvB85Oh2TZmVLtAblBL5KJsiTkoKToR1CNdu8zJ/WyjisDZDHZnb+ylBwiBAhuzZ2cFOi8eMexn8csCQQCM419HrA+P3shItnNcLQBZ/wFVy6YceTjLZSz8Q2dbBB2MGvhKwmMEACialsV0VAitj8kP6PSF0RIedrSrC1hh',
    'publicKey'  => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCoudS1qyGXy1og8ELFKH7KV8uio0WIR4iRQYUXxkVR4VRMBEmlKtgaPM8EKS8UwcS4K+C4N26g0GjZRDgPVVNcKgdVgUpNMpkFcaqkHAEGdnxiAao+wHc+pFEiibPjt49Us8v6NNt9SqpZngyi2BnPN6V8shZj8H2b0URaTDbsdwIDAQAB'
];

(new App($config));

// PEM 格式转换。
$privateKey = Crypt::privateKeyToPem(App::getConfig('privateKey'));
$publicKey  = Crypt::publicKeyToPem(App::getConfig('publicKey'));

// 待加密数据。
$data = [
    'method'    => 'user.login',     // 登录接口名称。
    'username'  => '14812345678',    // 账号。
    'password'  => '123456',         // 密码。
    'v'         => '1.0.0',          // 接口版本号。
    'timestamp' => time()            // 请求时间戳。
];
$dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

// 生成签名（RSA 非对称加密）。
$sign = Crypt::sign($dataJson, $privateKey);

echo "---- 签名结果 start ----\n";
echo $sign;
echo "\n---- 签名结果 end ----\n";
echo "\n";

// 加密数据（AES 对称加密）。
$ciphertext = Crypt::encrypt($dataJson, App::getConfig('secretKey')); // 默认是以 AES 128 位 CBC 分组模式加密。

echo "---- 加密结果 start ----\n";
echo $ciphertext;
echo "\n---- 加密结果 end ----\n";
echo "\n";

// 解密数据。
$decodeRes = Crypt::decrypt($ciphertext, App::getConfig('secretKey'));
echo "---- 解密结果 start ----\n";
echo $decodeRes;
echo "\n---- 解密结果 end ----\n";
echo "\n";

// 验证签名。
$signStatus = Crypt::verifySign($dataJson, $sign, $publicKey);
echo "\n---- 签名验证结果 start ----\n";
echo $signStatus ? '验证成功' : '验证失败';
echo "\n---- 签名验证结果 end ----\n";