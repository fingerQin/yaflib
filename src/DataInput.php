<?php
/**
 * 数据读取封装。
 * @author fingerQin
 * @date 2019-12-06
 */

namespace finger;

use finger\Exception\FingerException;

class DataInput
{
    /**
     * 从数组中读取一个数组。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  array   $defaultValue  默认值。
     * @return array
     */
    public static function getArray($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            throw new FingerException('值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                throw new FingerException("{$name} cannot be empty");
            } else if (!is_array($defaultValue)) {
                throw new FingerException("{$name} of the default value is not a array");
            } else {
                return $defaultValue;
            }
        } else {
            $value = $data[$name];
            if (!is_array($value)) {
                throw new FingerException("{$name} value is not a array");
            } else {
                return $value;
            }
        }
    }

    /**
     * 从数组中读取一个整型数值。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  int     $defaultValue  默认值。
     * @return int
     */
    public static function getInt($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            throw new FingerException('值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                throw new FingerException("{$name} cannot be empty");
            } else if (!Validator::is_integer($defaultValue)) {
                throw new FingerException("{$name} of the default value is not a integer");
            } else {
                return $defaultValue;
            }
        } else {
            $value = $data[$name];
            if (!Validator::is_integer($value)) {
                throw new FingerException("{$name} value is not a integer");
            } else {
                return $value;
            }
        }
    }

    /**
     * 从数组中读取一个字符串数值。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  string  $defaultValue  默认值。
     * @return string
     */
    public static function getString($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            throw new FingerException('值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                throw new FingerException("{$name} cannot be empty");
            } else {
                return $defaultValue;
            }
        } else {
            return $data[$name];
        }
    }

    /**
     * 从数组中读取一个整型数值。
     *
     * @param  array   $data          数组。
     * @param  string  $name          参数名称。
     * @param  float   $defaultValue  默认值。
     * @return float
     */
    public static function getFloat($data, $name, $defaultValue = null)
    {
        if (!is_array($data)) {
            throw new FingerException('值为空');
        }
        if (!isset($data[$name])) {
            if (is_null($defaultValue)) {
                throw new FingerException("{$name} cannot be empty");
            } else if (!Validator::is_float($defaultValue)) {
                throw new FingerException("{$name} of the default value is not a float");
            } else {
                return $defaultValue;
            }
        } else {
            $value = $data[$name];
            if (!Validator::is_float($value)) {
                throw new FingerException("{$name} value is not a float");
            } else {
                return $value;
            }
        }
    }
}