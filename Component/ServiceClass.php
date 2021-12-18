<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Awen\Bundles\Supports\ArrOperation;

class ServiceClass
{
    /**
     * 从数组中获取
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function getConfig($array, $key, $default = null)
    {
        return ArrOperation::get($array, $key, $default);
    }

    /**
     * 从数组中判断
     * @param $array
     * @param $key
     * @return bool
     */
    public static function hasConfig($array, $key)
    {
        return ArrOperation::has($array, $key);
    }

    /**
     * 设置到数组中
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function setConfig(&$array, $key, $value)
    {
        return ArrOperation::set($array, $key, $value);
    }

    /**
     * 从数组中获取值并删除它
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function pullConfig(&$array, $key, $default = null)
    {
        return ArrOperation::pull($array, $key, $default);
    }

    /**
     * 从数组中删除
     * @param $array
     * @param $keys
     */
    public static function forgetConfig(&$array, $keys)
    {
        ArrOperation::forget($array, $keys);
    }
}