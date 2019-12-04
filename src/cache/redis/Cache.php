<?php
/**
 * Redis缓存。
 * 
 * @author fingerQin
 * @date 2016-09-11
 */

namespace finger\cache\redis;

use finger\App;
use finger\Registry;
use finger\Validator;
use finger\Exception\CacheException;

class Cache
{
    /**
     * 当前对象。
     * @var finger\cache\redis
     */
    protected $client = null;

    /**
     * Redist 配置。
     *
     * @var array
     */
    protected $config = [];

    /**
     * 构造方法。
     *
     * @param string $redisOption Reids 配置项名称。
     *
     * @return void
     */
    public function __construct($redisOption = 'default')
    {
        // [1] 取配置。
        $config = App::getRedisConfig();
        if (empty($config)) {
            throw new CacheException('The redis cache configuration is not set');
        }
        $this->config = $config;
        // [2] 连接。
        $clientName = "finger_cache_redis_{$redisOption}";
        if (Registry::has($clientName)) {
            $redisIndex = $config[$redisOption]['index'] ?? 1; // 如果未设置默认为1。
            $this->client = Registry::get($clientName);
            $this->client->select($redisIndex); // 必须显示切换到指定的 Redis 库。避免使用过程中被其他程序切换未还原。
        } else {
            $this->client = $this->connect($redisOption);
            Registry::set($clientName, $this->client);
        }
    }

    /**
     * 获取 Redis 客户端连接。
     *
     * @return finger\cache\redis
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * 连接 redis
     * 
     * @param string $redisOption Reids 配置项名称。
     * 
     * @return \Redis
     */
    protected function connect($redisOption)
    {
        $config = $this->config[$redisOption] ?? [];
        if (empty($config)) {
            throw new CacheException("Redis 缓存配置:{$redisOption} 未设置");
        }
        $redisHost  = $config['host'];
        $redisPort  = $config['port'];
        $redisAuth  = $config['auth'];
        $redisIndex = $config['index'];
        $redis = new \Redis();
        $redis->connect($redisHost, $redisPort);
        $redis->auth($redisAuth);
        $redis->select($redisIndex);
        return $redis;
    }

    /**
     * 自增。
     * @param  string  $cacheKey  缓存 KEY。
     * @param  int     $step      自增步长。
     * @return int 自增之后的值。
     */
    public function incr($cacheKey, $step = 1)
    {
        if (!Validator::is_integer($step) || $step <= 0) {
            throw new CacheException('Redis incr step error');
        }
        return $this->client->incr($cacheKey, $step);
    }

    /**
     * 自减。
     * @param  string  $cacheKey  缓存 KEY。
     * @param  int     $step      自增步长。
     * @return int 自增之后的值。
     */
    public function decr($cacheKey, $step = 1)
    {
        if (!Validator::is_integer($step) || $step <= 0) {
            throw new CacheException('Redis decr step error');
        }
        return $this->client->decr($cacheKey, $step);
    }

    /**
     * 获取缓存。
     * @param  string  $cacheKey  缓存 KEY。
     * @return string|array|bool
     */
    public function get($cacheKey)
    {
        $cacheData = $this->client->get($cacheKey);
        return $cacheData ? json_decode($cacheData, true) : false;
    }

    /**
     * 写缓存。
     * @param  string        $cacheKey   缓存 KEY。
     * @param  string|array  $cacheData  缓存数据。
     * @param  integer       $expire     生存时间。单位(秒)。0 代表永久生效。
     * @return bool
     */
    public function set($cacheKey, $cacheData, $expire = 0)
    {
        if ($expire > 0) {
            return $this->client->setEx($cacheKey, $expire, json_encode($cacheData));
        } else {
            return $this->client->set($cacheKey, json_encode($cacheData));
        }
    }

    /**
     * 删除缓存。
     * @param  string  $cacheKey
     * @return bool
     */
    public function delete($cacheKey)
    {
        return $this->client->del($cacheKey);
    }
}