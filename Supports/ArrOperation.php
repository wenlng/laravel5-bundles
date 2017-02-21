<?php

namespace Awen\Bundles\Supports;

class ArrOperation
{
    /**
     * 判断一个key是否存在数组中
     * @param $array
     * @param $key
     * @return mixed
     */
    public static function exists($array, $key)
    {
        return array_key_exists($key, $array);
    }

    /**
     * 从数组中获取
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * 从数组中判断
     * @param $array
     * @param $key
     * @return bool
     */
    public static function has($array, $key)
    {
        if (!$array) {
            return false;
        }

        if (is_null($key)) {
            return false;
        }

        if (static::exists($array, $key)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * 设置到数组中
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 从数组中获取值并删除它
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    /**
     * 从数组中删除
     * @param $array
     * @param $keys
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array)$keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

}