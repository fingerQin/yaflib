<?php
/**
 * 封装一个与 Yaf_Registry 类功能相似的功能。
 * 
 * @author 7031
 * @date 2019-11-27
 */

namespace finger;

class Registry
{
    /**
     * 存放注册进来的数据。
     *
     * @var array
     */
    private static $data = null;

    /**
     * 查询某一项目是否存在于注册表中。
     *
     * @param string $name 要查询的项的名字。
     *
     * @return bool
     */
    public static function has($name)
    {
        return isset(self::$data[$name]) ? true : false;
    }

    /**
     * 要获取的项的名字。
     *
     * @param string $name 要获取的项的名字。
     *
     * @return mixed
     */
    public static function get($name)
    {
        return self::$data[$name] ?? false;
    }

    /**
     * 往全局注册表添加一个新的项。
     *
     * @param string $name  要注册的项的名字。
     * @param string $value 要注册的项的值。
     *
     * @return void
     */
    public static function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    /**
     * 删除存在于注册表中的名为 $name 的项目。
     *
     * @param string $name 要删除的项的名字。
     *
     * @return bool
     */
    public static function del($name)
    {
        unset(self::$data[$name]);
        return true;
    }
}