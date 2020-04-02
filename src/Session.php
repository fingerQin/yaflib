<?php
/**
 * Session 操作封装。
 * 
 * -- 该 Session 默认存储在 Redis 配置指定的 default 项的对应的 Redis 缓存中。
 * 
 * @author fingerQin
 * @date 2019-12-06
 */

namespace finger;

use finger\Exception\SessionException;
use finger\Session\Redis\SessionHandler;

class Session
{
    /**
     * 是否已启用 Session。
     *
     * @var boolean
     */
    private static $status = false;

    /**
     * 设置。
     *
     * @param  string        $Key    名称。
     * @param  string|array  $value  值。
     *
     * @return bool
     */
    public static function set($key, $value)
    {
        self::isOpenSession();
        return Registry::get('session')->set($key, $value);
    }

    /**
     * 读取 SESSION。
     *
     * @param  string  $key  名称。
     * 
     * @return bool
     */
    public static function get($key)
    {
        self::isOpenSession();
        return Registry::get('session')->get($key);
    }

    /**
     * 删除 SESSION。
     *
     * @param  string  $key  名称。
     * @return bool
     */
    public static function delete($key)
    {
        self::isOpenSession();
        return Registry::get('session')->del($key);
    }

    /**
     * 清空 SESSION。
     *
     * @param  string  $key  名称。
     * @return bool
     */
    public static function destroy()
    {
        self::isOpenSession();
        return session_destroy();
    }

    /**
     * 判断当前是否打开了 SESSION。
     * 
     * -- 未打开直接报错提示去打开 SESSION。
     */
    private static function isOpenSession()
    {
        if (!App::getConfig('session.status')) {
            throw new SessionException('Please open the session switch : session.status');
        } elseif (self::$status === false) {
            self::$status = true;
            $redis = Cache::getRedisClient();
            $sess  = new SessionHandler($redis, null, 'sess_');
            session_set_save_handler($sess);
        }
    }
}