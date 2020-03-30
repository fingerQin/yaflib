<?php
/**
 * 当前库启动入口程序。
 * 
 * -- 主要是为了方便初始化一些数据进来便于后面使用。
 * 
 * @author fingerQin
 * @date 2019-12-03
 */

namespace finger;

class App
{
    /**
     * 当前所运行应用的根路径。
     *
     * -- 因为框架默认以 Composer 加载运行。所以，根路径会以 Composer 结构往上推。
     * 
     * @var string
     */
    private static $rootPath = '';

    /**
     * 调试模式。
     * 
     * -- 调试模式会影响日志等相关功能的性状。
     *
     * @var boolean
     */
    private static $debug = true;

    /**
     * 数据库配置。
     * 
     * ```
     * $dbConfig = [
     *     'default' => [
     *         'host'     => 'string:数据库主机地址',
     *         'port'     => 'int:数据库主机端口',
     *         'user'     => 'string:数据库账号',
     *         'pwd'      => 'string:数据库密码',
     *         'dbname'   => 'string:数据库名称',
     *         'charset'  => 'string:数据库字符集',
     *         'pconnect' => 'bool:是否长连接'
     *     ],
     *     'pay' => [
     *         'host'     => 'string:数据库主机地址',
     *         'port'     => 'int:数据库主机端口',
     *         'user'     => 'string:数据库账号',
     *         'pwd'      => 'string:数据库密码',
     *         'dbname'   => 'string:数据库名称',
     *         'charset'  => 'string:数据库字符集',
     *         'pconnect' => 'bool:是否长连接'
     *     ],
     *     ......
     * ];
     * ```
     * 
     * @var array
     */
    private static $dbConfig = [];

    /**
     * Redis 配置。
     * 
     * ```
     * $redisConfig = [
     *     'default' => [
     *         'host'  => 'string:Redis主机地址',
     *         'port'  => 'int:Redis主机端口',
     *         'auth'  => 'string:Redis密码',
     *         'index' => 'int:索引'
     *     ],
     *     'other' => [
     *         'host'  => 'string:Redis主机地址',
     *         'port'  => 'int:Redis主机端口',
     *         'auth'  => 'string:Redis密码',
     *         'index' => 'int:索引'
     *     ],
     *     ......
     * ];
     * ```
     *
     * @var array
     */
    private static $redisConfig = [];

    /**
     * 存储传入的配置。
     *
     * @var array
     */
    private static $appConfig = [];

    /**
     * 构造函数。
     *
     * @param array $config 配置。
     * 
     * @return void
     */
    public function __construct(array $config = [])
    {
        self::$appConfig = $config;
        self::setDebug($config['app']['debug'] ?? true);
        self::setRootPath($config['app']['root_path'] ?? '');
        self::setDbConfig($config['mysql'] ?? []);
        self::setRedisConfig($config['redis'] ?? []);
    }

    /**
     * 设置调试模式。
     *
     * @param boolean $debug
     *
     * @return void
     */
    private static function setDebug($debug = true)
    {
        self::$debug = $debug;
    }

    /**
     * 是否为调试模式(默认为是)。
     * 
     * @return boolean
     */
    public static function isDebug()
    {
        return self::$debug;
    }

    /**
     * 设置数据库配置。
     *
     * @param array $dbConfig
     *
     * @return void
     */
    private static function setDbConfig(array $dbConfig = [])
    {
        self::$dbConfig = $dbConfig;   
    }

    /**
     * 返回数据库配置。
     *
     * @return array
     */
    public static function getDbConfig()
    {
        return self::$dbConfig;
    }

    /**
     * 设置项目根路径。
     *
     * @param string $path
     *
     * @return void
     */
    private static function setRootPath($path = '')
    {
        if ($path) {
            self::$rootPath = $path;
        } elseif (defined('APP_PATH')) {
            self::$rootPath = APP_PATH;
        } else {
            self::$rootPath = dirname(dirname(dirname(dirname(__DIR__))));
        }
    }

    /**
     * 获取根路径。
     *
     * @return string
     */
    public static function getRootPath()
    {
        return rtrim(self::$rootPath, "/\\");
    }

    /**
     * 设置 Redis 配置文件。
     *
     * @param array $redisConfig
     * @return void
     */
    private static function setRedisConfig(array $redisConfig = [])
    {
        self::$redisConfig = $redisConfig;
    }

    /**
     * 获取 Redist 配置。
     *
     * @return array
     */
    public static function getRedisConfig()
    {
        return self::$redisConfig;
    }

    /**
     * 获取配置。
     *
     * @param  string  $name          配置项名称。支持中间带点号形式获取配置：mysql.default 
     * @param  mixed   $defaultValue  默认值。
     * 
     * @return mixed 取不到配置返回 NULL。
     */
    public static function getConfig($name, $defaultValue = NULL)
    {
        $cfgNameArr = explode('.', $name);
        $cfgLength  = count($cfgNameArr);
        if ($cfgLength == 1) {
            return self::$appConfig[$name] ?? $defaultValue;
        } else {
            $cfgValue = null; // 临时存储每一级的配置。
            for ($i = 0; $i < $cfgLength; $i++) {
                $cfgName = $cfgNameArr[$i]; // 当前配置项名称。
                if ($i == ($cfgLength-1)) { // 循环到最后一个配置项的时候直接判断并返回其值。
                    return $cfgValue[$cfgName] ?? NULL;
                } else {
                    if (!is_null($cfgValue)) { // 如果这个值有值，代表上一级的配置是有值的。
                        if (isset($cfgValue[$cfgName])) {
                            $cfgValue = $cfgValue[$cfgName];
                        } else {
                            return $defaultValue ?? NULL;
                        }
                    } elseif (isset(self::$appConfig[$cfgName])) { // 第一次进来运行这里。
                        $cfgValue = self::$appConfig[$cfgName];
                    } else {
                        return $defaultValue ?? NULL;
                    }
                }
            }
        }
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
    public static function log($logContent, $logDir = '', $logFilename = '', $isForceWrite = false)
    {
        Log::save($logContent, $logDir, $logFilename, $isForceWrite);
    }

    /**
     * 判断是否为 CLI 模式运行。
     *
     * @return boolean
     */
    public static function isCli()
    {
        return preg_match("/cli/i", PHP_SAPI) ? TRUE : FALSE;
    }
}