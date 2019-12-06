<?php
/**
 * 核心的公共工具方法。
 * @author fingerQin
 * @date 2019-12-06
 */

namespace finger;

class Core
{
    /**
     * 忽略不处理的错误。
     *
     * -- 在 PHP 底层有一些异常抛出的时候，同时会触发一个错误。导致我们异常与错误处理同时生效而陷入逻辑处理的困难。
     * -- 忽略之后，可以实现只处理异常。
     * 
     * @var array
     */
    protected static $ignoreError = [
        'server has gone away',
        'no connection to the server',
        'Lost connection',
        'is dead or not enabled',
        'Error while sending',
        'decryption failed or bad record mac',
        'server closed the connection unexpectedly',
        'SSL connection has been closed unexpectedly',
        'Error writing data to the connection',
        'Resource deadlock avoided',
        'Transaction() no null',
        'child connection forced to terminate due to client_idle_limit',
        'query_wait_timeout',
        'reset by peer',
        'Physical connection is not usable',
        'TCP Provider: Error code 0x68',
        'Name or service not known'
    ];

    /**
     * 是否属于忽略性的错误。
     * 
     * @param  string  $errMsg  错误信息。
     *
     * @return bool
     */
    protected static function isIgnoreError($errMsg)
    {
        foreach (self::$ignoreError as $msg) {
            if (strpos($errMsg, $msg) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 抛出异常。
     * 
     * @param  int            $errCode             错误编号。
     * @param  string|array   $errMsg              错误信息。
     * @param  string         $classNameAndMethod  出错位置执行的类与方法。当使用 try cacth 捕获异常时将捕获的异常信息传入。
     * @param  string         $args                出错位置传入方法的参数。当使用 try cacth 捕获异常时将捕获的异常信息传入。
     * @throws \finger\Exception\ServiceException
     */
    public static function exception($errCode, $errMsg, $classNameAndMethod = '', $args = [])
    {
        if (strlen($classNameAndMethod) === 0) {
            // debug_backtrace() 返回整个堆栈调用信息。
            // 堆栈里面的第二个数组返回的是调用 Core::exception() 方法所在的类与方法相关信息。
            $result             = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $classNameAndMethod = $result[1]['class'] . $result[1]['type'] . $result[1]['function'];
            $args               = $result[1]['args'];
        }
        throw new \finger\Exception\ServiceException($errMsg, $errCode, $classNameAndMethod, $args);
    }

    /**
     * 定义一个PHP set_error_handler 的错误回调函数。
     *
     * @param  int     $errno    错误的级别。
     * @param  string  $errstr   错误的信息。
     * @param  string  $errfile  发生错误的文件名。
     * @param  int     $errline  错误发生的行号。
     * @return void
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * 定义一个PHP register_shutdown_function 回调方法。
     * 
     * --1) 当 PHP 发生语法级别错误时会调用该方法(已在 Yaf Bootstrap 中调用)。
     * --2) 每次 PHP 脚本执行结束判断是否存在语法错误。有就收集错误信息记录日志。
     * --3) 如果是 API 调用则返回 API 规定的 JSON 格式。
     * --4) 如果是非 API 调用则显示 HTTP status 500 错误。
     *
     * @return void
     */
    public static function registerShutdownFunction()
    {
        $errInfo = error_get_last();
        if (!empty($errInfo)) {
            // [1] 获取堆栈信息。
            $debugStack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
            $traceStack = '';
            foreach ($debugStack as $debug) {
                if (isset($debug['file'])) {
                    $traceStack .= "#{$debug['file']} line {$debug['line']}\n";
                }
            }
            // [2] 根据环境配置输出不同错误信息。
            $serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
            $clientIP = Ip::ip();
            $appDebug = App::isDebug();
            $isCli    = App::isCli();

            $logData = [
                'ErrorTime'  => date('Y-m-d H:i:s'),
                'Type'       => 'register_shutdown_function',
                'ServerIP'   => $serverIP,
                'ClientIP'   => $clientIP,
                'ErrorFile'  => $errInfo['file'],
                'ErrorLine'  => $errInfo['line'],
                'ErrorMsg'   => $errInfo['message'],
                'ErrorNo'    => $errInfo['type'], 
                'stackTrace' => $traceStack
            ];

            App::log($logData, 'errors', 'log', $isForceWrite = true);
            if (defined('IS_API')) {
                ob_clean();
                header("Access-Control-Allow-Origin: *");
                header('Content-type: application/json');
                $data = [
                    'code' => 500,
                    'msg'  => $appDebug ? print_r($logData, true) : '服务器繁忙,请稍候重试'
                ];
                Log::writeApiResponseLog($data);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } else if ($isCli) {
                $datetime = date('Y-m-d H:i:s', time());
                echo $datetime . "\n" . print_r($logData, true);
            } else {
                ob_clean();
                if ($appDebug) {
                    echo print_r($logData, true);
                } else {
                    header('HTTP/1.1 500 Internal Server Error');
                }
            }
        }
        exit(0);
    }

    /**
     * 根据两点间的经纬度计算距离
     * -- 1、纬度最大值为90度，经度最大值为180度。
     * -- 2、只要其中一个值为-1则返回0.这是特殊约定的业务逻辑。
     *
     * @param  float $lat  纬度值。
     * @param  float $lng  经度值。
     * @param  float $lat2 纬度值2。
     * @param  float $lng2 经度值2。
     * @return int
     */
    public static function distance($lat1, $lng1, $lat2, $lng2)
    {
        if ($lat1 == -1 || $lng1 == -1 || $lat2 == -1 || $lng2 == -1) {
            return 0;
        }
        $earthRadius = 6371000; // approximate radius of earth in meters
        $lat1          = ($lat1 * pi()) / 180;
        $lng1          = ($lng1 * pi()) / 180;
        $lat2          = ($lat2 * pi()) / 180;
        $lng2          = ($lng2 * pi()) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude  = $lat2 - $lat1;
        $stepOne       = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo       = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    /**
     * 获取远程内容
     * 
     * -- 在使用类似方法或 CURL 的时候。如果确定不使用 IPV6 解析，请关闭它。
     *
     * @param  string  $url      接口url地址
     * @param  int     $timeout  超时时间
     * @return string
     */
    public static function pc_file_get_contents($url, $timeout = 30)
    {
        $stream = stream_context_create([
            'http' => [
                'timeout' => $timeout
            ]
        ]);
        return @file_get_contents($url, 0, $stream);
    }

    /**
     * 递归计算一个数值。
     * @param  int $a 数值。
     * @return int
     */
    public static function factorial($a)
    {
        if ($a > 1) {
            $r = $a * self::factorial($a - 1);
        } else {
            $r = $a;
        }
        return $r;
    }

    /**
     * 获取身份证号对应的生日信息。
     *
     * @param  string  $idCardNo  身份证号。
     * @return string
     */
    public static function getIdCardNoBirthday($idCardNo)
    {
        if (strlen($idCardNo) === 0) {
            return null;
        }
        $year  = substr($idCardNo, 6, 4);
        $month = substr($idCardNo, 10, 2);
        $day   = substr($idCardNo, 12, 2);
        return "{$year}-{$month}-{$day}";
    }

    /**
     * 随机指定个数的整数范围值。
     * 
     * --1、范围段数值个数小于等于要取的个数的10倍，则直接使用shuffle方式获取。
     * --2、范围段数值个数大于要取的个数的10倍，则每个数值都随机产生。并去重。
     * --3、以上两点一是为了性能，二是为了能避免无效的随机值。
     *
     * @param  int  $min    范围最小值(含)。
     * @param  int  $max    范围小大值(含)。
     * @param  int  $count  要取的值个数。
     * 
     * @return array
     */
    public static function randomIntegerScope($min, $max, $count = 20)
    {
        $validCount = ($max - $min) + 1; // 包含边界值。所以，要加1。
        if ($validCount <= $count * 10) {
            $scopeVal = array_fill($min, $validCount, 0);
            $keys = array_keys($scopeVal);
            shuffle($keys);
            return array_slice($keys, 0, $count);
        } else {
            $randVals = [];
            while(true) {
                $randVal = mt_rand($min, $max);
                if (!in_array($randVal, $randVals)) {
                    $randVals[] = $randVal;
                    if (count($randVals) == $count) {
                        break;
                    }
                }
            }
            return $randVals;
        }
    }

    /**
     * 当数组为空时转换为空对象。
     *
     * @param  array  $data
     *
     * @return array|object
     */
    public static function dataToNullObject($data = [])
    {
        return !empty($data) ? $data : self::getNullObject();
    }

    /**
     * 获取一个空对象。
     * 
     * @return object
     */
    public static function getNullObject()
    {
        return (object)[];
    }
}