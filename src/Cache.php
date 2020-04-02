<?php
/**
 * 缓存操作。
 * @author fingerQin
 * @date 2019-12-06
 */

namespace finger;

use finger\Registry;
use finger\Cache\Redis\Cache AS RedisCache;
use finger\Exception\CacheException;

class Cache
{
    /**
     * 初始化缓存对象。
     * 
     * @param string $redisOption Redis 配置项。
     *
     * @return \finger\Cache\Redis\Cache
     */
    private static function getInstace($redisOption = 'default')
    {
        $requestKey = "__system__cache__{$redisOption}";
        $ok = Registry::has($requestKey);
        if ($ok) {
            return Registry::get($requestKey);
        } else {
            $systemCache = new RedisCache($redisOption);
            Registry::set($requestKey, $systemCache);
            return $systemCache;
        }
    }

    /**
     * 获取 Redis 对象。
     * 
     * -- 我们通常会用一些高级的操作虽然直接调用底层提供的方法。
     * 
     * @param string $redisOption Redis 配置项。
     *
     * @return \Reids
     */
    public static function getRedisClient($redisOption = 'default')
    {
        $cache = self::getInstace($redisOption);
        return $cache->getClient();
    }

    /**
     * 缓存设置。
     *
     * @param  string        $Key    缓存键。
     * @param  string|array  $value  缓存值。
     * @param  int           $time   缓存时间。单位(秒)。
     * @return void
     */
    public static function set($key, $value, $time = 0)
    {
        $ret = (self::getInstace())->set($key, $value, $time);
        if ($ret != true) {
            throw new CacheException('Redis set method call failed');
        }
    }

    /**
     * 读取缓存。
     *
     * @param  string  $key  缓存键。
     * @return string|array|bool
     */
    public static function get($key)
    {
        return (self::getInstace())->get($key);
    }

    /**
     * 删除缓存。
     *
     * @param  string  $key  缓存键。
     * @return void
     */
    public static function delete($key)
    {
        return (self::getInstace())->delete($key);
    }

    /**
     * 自增。
     *
     * @param  string  $key   缓存键。
     * @param  int     $step  自增步长。
     * @return int
     */
    public static function incr($key, $step = 1)
    {
        return (self::getInstace())->incr($key, $step);
    }

    /**
     * 自减。
     *
     * @param  string  $key   缓存键。
     * @param  int     $step  自增步长。
     * @return int
     */
    public static function decr($key, $step = 1)
    {
        return (self::getInstace())->decr($key, $step);
    }

    /**
     * Redis 存活检测。
     *
     * @param string $redisOption Redis 配置项。
     * 
     * @return void
     */
    public static function ping($redisOption = 'default')
    {
        $redis = self::getRedisClient($redisOption);
        $pong  = $redis->ping();
        if ($pong != '+PONG') {
            App::log('Redis ping failure!postion:dispatcher', 'redis', 'ping');
            throw new CacheException('Redis ping failure!');
        }
    }
}