<?php
/**
 * 验证器异常类。
 *
 * @author fingerQin
 * @date 2019-11-07
 */

namespace finger\Exception;

use finger\Utils\YCore;
use finger\Utils\YUrl;

class ValidatorException extends FingerException
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
    public function __construct($message, $code = 0, $classNameAndMethod = '', $args = [], Exception $previous = null)
    {
        $code = intval($code);
        parent::__construct($message, $code, $previous);
        $this->classNameAndMethod = $classNameAndMethod;
        $this->methodArgs = $args;
    }

    /**
     * 重写 __toString()。
     * @return string
     */
    public function __toString()
    {
        $serverIP   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $clientIP   = YCore::ip();
        $requestUrl = YUrl::getUrl();
        $datetime   = date('Y-m-d H:i:s', time());
        $errLog     = "ErrorTime:{$datetime} \n";
        $errLog    .= "ErrorMsg: {$this->message} \n";
        $errLog    .= "ErrorCode: [{$this->code}] \n";
        $errLog    .= "ServerIP: [{$serverIP}] \n";
        $errLog    .= "ClientIP: [{$clientIP}] \n";
        $errLog    .= "RequestUrl: [{$requestUrl}] \n";
        $errLog    .= "Method: {$this->classNameAndMethod}\n";
        $errLog    .= "Params:\n" . print_r($this->methodArgs, true) . "\n";
        $errLog    .= "StackTrace:\n" . $this->getTraceAsString();
        return $errLog;
    }
}