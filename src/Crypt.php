<?php
/**
 * 加解密。
 * @author fingerQin
 * @date 2019-12-06
 */

namespace finger;

use finger\Exception\CryptException;

class Crypt
{
    /**
     * AES 加密。
     *
     * @param  string  $plaintext   明文。
     * @param  string  $key         密钥。
     * @param  string  $cipher      加密算法。aes-128-ecb、aes-192-ecb、aes-256-ecb
     *
     * @return string
     */
    public static function encrypt($plaintext, $key, $cipher = 'AES-128-ECB')
    {
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv    = openssl_random_pseudo_bytes($ivlen);
        return openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv);
    }

    /**
     * AES 解密。
     *
     * @param  string  $ciphertext  密文。
     * @param  string  $key         密钥。
     * @param  string  $cipher      加密算法。aes-128-ecb、aes-192-ecb、aes-256-ecb
     *
     * @return void
     */
    public static function decrypt($ciphertext, $key, $cipher = 'AES-128-ECB')
    {
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv    = openssl_random_pseudo_bytes($ivlen);
        return openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv);
    }

    /**
     * (公钥)验证签名。
     *
     * @param  string  $data       待验证数据。
     * @param  string  $sign       待验证签名。
     * @param  string  $publicKey  公钥。
     *
     * @return bool
     */
    public static function verifySign($data, $sign, $publicKey)
    {
        $signature = base64_decode($sign); 
        return (openssl_verify($data, $signature, $publicKey, OPENSSL_PKCS1_PADDING) == 1) ? true : false;
    }

    /**
     * (私钥)生成签名。
     *
     * @param  string  $plaintext   明文字符串。如果是数字请自行转换为字符串。
     * @param  string  $privateKey  私钥。
     *
     * @return string
     */
    public static function sign($plaintext, $privateKey)
    {
        $signature = '';
        if (openssl_sign($plaintext, $signature, $privateKey, OPENSSL_PKCS1_PADDING)) {
            $sign = base64_encode($signature);
        } else {
            throw new CryptException('Signature generation failed');
        }
        return $sign;
    }

    /**
     * 字符串加密、解密函数。
     * 
     * -- 对称加密(不推荐使用)。
     *
     * @param  string   $txt          字符串
     * @param  string   $operation    ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
     * @param  string   $key          密钥：数字、字母、下划线
     * @param  string   $expiry       过期时间
     * @return string
     */
    public static function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0)
    {
        $key_length    = 4;
        $key           = md5($key != '' ? $key : App::getConfig('app.key'));
        $fixedkey      = md5($key);
        $egiskeys      = md5(substr($fixedkey, 16, 16));
        $runtokey      = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), - $key_length) : substr($string, 0, $key_length)) : '';
        $keys          = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
        $string        = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));
        $i             = 0;
        $result        = '';
        $string_length = strlen($string);
        for ($i = 0; $i < $string_length; $i ++) {
            $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
        }
        if ($operation == 'ENCODE') {
            return $runtokey . str_replace('=', '', base64_encode($result));
        } else {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $egiskeys), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        }
    }

    /**
     * 字符串格式公钥转换成 PEM 格式公钥。
     *
     * -- 在 PHP 中 openssl 只支持 PEM 格式的公钥。所以，需要非 PEM 格式字符串公钥转换为 PEM 格式。
     * -- 所谓 PEM 格式是首尾会存在特定的标识，并且公钥会按照每 64 个字符进行换行。
     *
     * @author fingerQin
     *
     * @param string $publicKey 非 PEM 格式字符串公钥。
     *
     * @return string
     */
    public static function publicKeyToPem($publicKey)
    {
        $str = chunk_split($publicKey, 64, "\n");
        $str = "-----BEGIN PUBLIC KEY-----\n" . $str;
        return $str . "-----END PUBLIC KEY-----";
    }

    /**
     * 字符串格式私钥转换成 PEM 格式私钥(PKCS#8)。
     *
     * -- 现在 PHP 领域或者其他平台都会使用 RSA PKCS#8 格式的私钥。
     * -- 在 PHP 中 openssl 只支持 PEM 格式的私钥。所以，需要非 PEM 格式字符串私钥转换为 PEM 格式。
     * -- 所谓 PEM 格式是首尾会存在特定的标识，并且私钥会按照每 64 个字符进行换行。
     *
     * @author fingerQin 2019-12-06
     *
     * @param string $privateKey 私钥。
     *
     * @return string
     */
    public static function privateKeyToPem($privateKey)
    {
        $str = chunk_split($privateKey, 64, "\n");
        $str = "-----BEGIN RSA PRIVATE KEY-----\n" . $str;
        return $str . "-----END RSA PRIVATE KEY-----";
    }
}