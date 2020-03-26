<?php
/**
 * 类库异常基类。
 * 
 * @author fingerQin
 * @date 2019-11-07
 */

namespace finger\Exception;

use finger\Ip;
use finger\Url;

class FingerException extends \Exception
{
    /**
     * 错误信息以数据返回。
     * 
     * -- 通常用于特殊环境解析 JSON 存入日志系统用。
     *
     * @return array
     */
    public function __toArray()
    {
        $serverIP   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $clientIP   = Ip::ip();
        $requestUrl = Url::getUrl();
        $datetime   = date('Y-m-d H:i:s', time());
        $errors = [
            'err_time'    => $datetime,
            'err_msg'     => $this->message,
            'err_code'    => $this->code,
            'service_ip'  => $serverIP,
            'client_ip'   => $clientIP,
            'req_url'     => $requestUrl,
            'stack_trace' => $this->getTraceAsString()
        ];
        return $errors;
    }

    /**
     * 重写 __toString()。
     * @return string
     */
    public function __toString()
    {
        $serverIP   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $clientIP   = Ip::ip();
        $requestUrl = Url::getUrl();
        $datetime   = date('Y-m-d H:i:s', time());
        $errLog     = "err_time:{$datetime} \n";
        $errLog    .= "err_msg: {$this->message} \n";
        $errLog    .= "err_code: [{$this->code}] \n";
        $errLog    .= "server_ip: [{$serverIP}] \n";
        $errLog    .= "client_ip: [{$clientIP}] \n";
        $errLog    .= "req_url: [{$requestUrl}] \n";
        $errLog    .= "stack_trace:\n" . $this->getTraceAsString();
        return $errLog;
    }
}