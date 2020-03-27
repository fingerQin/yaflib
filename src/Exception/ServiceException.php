<?php
/**
 * 系统业务异常类。
 *
 * --1、此业务异常类要实现的目标是收集异常所处位置的类、方法、方法的参数、以及相应的 trace 信息。 
 *
 * @author fingerQin
 * @date 2019-11-07
 */

namespace finger\Exception;

use finger\Ip;
use finger\Url;

class ServiceException extends FingerException
{
    protected $classNameAndMethod = '';
    protected $methodArgs         = [];

    /**
     * 构造方法。
     * @param  string         $message            错误信息。
     * @param  int            $code               错误编码。
     * @param  string         $classNameAndMethod 类名与类方法。格式: User::register
     * @param  array          $args               方法参数。通过此参数可以记录到日志当中,定位问题可以反推现场。
     * @param  Exception|null $previous
     * @return void
     */
    public function __construct($message, $code = 0, $classNameAndMethod = '', $args = [], \Throwable $previous = null)
    {
        $code = intval($code);
        parent::__construct($message, $code, $previous);
        $this->classNameAndMethod = $classNameAndMethod;
        $this->methodArgs = $args;
    }

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
            'server_ip'   => $serverIP,
            'client_ip'   => $clientIP,
            'req_url'     => $requestUrl,
            'method'      => $this->classNameAndMethod,
            'params'      => json_encode($this->methodArgs),
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
        $errLog    .= "method: {$this->classNameAndMethod}\n";
        $errLog    .= "params:\n" . print_r($this->methodArgs, true) . "\n";
        $errLog    .= "stack_trace:\n" . $this->getTraceAsString();
        return $errLog;
    }
}