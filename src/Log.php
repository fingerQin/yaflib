<?php
/**
 * 应用 API 日志处理。
 * 
 * @author fingerQin
 * @date 2017-08-08
 * @modify fingerQin 2018-11-28 将多次调用写日志的方法合并为 Log 对象销毁时一次性写入。
 */

namespace finger;

class Log
{
    /**
     * 日志记录类型。
     */
    const LOG_WRITE_TYPE_RAW  = 'raw';  // 原生：即以语言相关的数组打印形式写入日志。
    const LOG_WRITE_TYPE_JSON = 'json'; // JSON。

    /**
     * 当前对象实例。
     *
     * @var finger\Log
     */
    private static $_instance;

    /**
     * 日志内容。
     * 
     * -- 因为日志会存放多个目录下的文件里面。所以，用数组来区分不同目录与文件。
     *
     * @var array
     */
    private $logCtx = [];

    private function __construct() {}

    /**
     * 防止克隆导致单例失败。
     * 
     * @return void
     */
    private function __clone() {}

    /**
     * 获取当前对象实例。
     * 
     * @return finger\Log
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self)) {    
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 记录 API 接口请求日志。
     * 
     * @param  array  $params  请求参数。
     * @return void
     */
    public static function writeApiRequestLog($params)
    {
        ksort($params);
        $GLOBALS['Server-api'] = $params;
    }

    /**
     * 记录 API 接口响应的数据日志。
     * 
     * @param  string  $result  响应 JOSN 数据。
     * @return void
     */
    public static function writeApiResponseLog($result)
    {
        $requestLog = isset($GLOBALS['Server-api']) ? $GLOBALS['Server-api'] : [];
        $requestLog['_response_date'] = date('Y-m-d H:i:s', time());
        $log = [
            'request'  => $requestLog,
            'response' => $result
        ];
        unset($GLOBALS['Server-api']);
        self::save($log, 'apis', 'log');
    }

    /**
     * 写日志。
     *
     * @param  string|array  $logContent    日志内容。
     * @param  string        $logDir        日志目录。如：bank
     * @param  string        $logFilename   日志文件名称。如：bind。生成文件的时候会在 bind 后面接上日期。如:bind-20171121.log
     * @param  bool          $isForceWrite  是否强制写入硬盘。默认值：false。设置为 true 则日志立即写入硬盘而不是等待析构函数回收再执行。
     *
     * @return void
     */
    public static function save($logContent, $logDir = '', $logFilename = '', $isForceWrite = false)
    {
        $time    = time();
        $logTime = date('Y-m-d H:i:s', $time);
        if (!is_array($logContent)) {
            $serverIP   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
            $clientIP   = Ip::ip();
            $logContent = [
                'ErrorTime' => $logTime,
                'ServerIP'  => $serverIP,
                'ClientIP'  => $clientIP,
                'content'   => $logContent
            ];
        } else {
            $logContent = array_merge(['ErrorTime' => $logTime], $logContent);
        }
        $logfile = date('Ymd', $time);
        if (strlen($logDir) > 0 && strlen($logFilename) > 0) {
            $logDir   = trim($logDir, '/');
            $logPath  = App::getRootPath() . '/logs/' . $logDir;
            Dir::create($logPath);
            $logPath .= "/{$logFilename}-{$logfile}.log";
        } else {
            $logPath  = App::getRootPath() . '/logs/errors/';
            Dir::create($logPath);
            $logPath  = $logPath . $logfile . '.log';
        }
        if (App::getConfig('log.type') == Log::LOG_WRITE_TYPE_JSON) {
            $logCtx = json_encode($logContent, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\n\n";
        } else {
            $logCtx = print_r($logContent, true) . "\n\n";
        }
        $logObj = Log::getInstance();
        $logObj->write($logCtx, $logPath, $isForceWrite);
    }

    /**
     * 写日志(只是暂存,不会直接写入)。
     *
     * @param  string  $log           日志内容。
     * @param  string  $logPath       日志保存路径。
     * @param  bool    $isForceWrite  是否强制写入硬盘。默认值：false。设置为 true 则日志立即写入硬盘而不是等待析构函数回收再执行。
     *
     * @return void
     */
    public function write($log, $logPath, $isForceWrite = false)
    {
        if (PHP_SAPI == 'cli' || $isForceWrite) { // Cli 模式立即写入硬盘。
            file_put_contents($logPath, $log, FILE_APPEND);
        } else {
            $arrLogKey = md5($logPath);
            if (isset($this->logCtx[$arrLogKey])) {
                $this->logCtx[$arrLogKey]['log'] = $this->logCtx[$arrLogKey]['log'] . $log;
            } else {
                $this->logCtx[$arrLogKey] = [
                    'logPath' => $logPath,
                    'log'     => $log
                ];
            }
        }
    }

    /**
     * 对象销毁时写入日志到文件。
     */
    public function __destruct()
    {
        // [1]
        $openedFileHandle = []; // 保存已经打开的文件句柄。
        foreach($this->logCtx as $key => $logObj) {
            if (isset($openedFileHandle[$key])) { // 已打开了文件，则直接写入。
                fwrite($openedFileHandle[$key], $logObj['logPath']);
            } else {
                Dir::create(dirname($logObj['logPath']));
                $handle = fopen($logObj['logPath'], 'a');
                $openedFileHandle[$key] = $handle;
                fwrite($handle, $logObj['log']);
            }
        }
        // [2] 关闭已打开的文件句柄。
        foreach($openedFileHandle as $hd) {
            fclose($hd);
        }
    }
}