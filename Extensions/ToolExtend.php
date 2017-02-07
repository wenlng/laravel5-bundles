<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2016-01-26
 * Time: 18:25
 */

namespace Awen\Bundles\Extensions;
use Illuminate\Support\Str;

/**工具扩展类
 * Class ToolExtend
 * @package Awen\Bundles\Extensions
 */
class ToolExtend
{
    /**
     * 获取路径
     * @return mixed
     */
    public function getCurrentPath()
    {
        $reflected = new \ReflectionObject($this);
        return dirname($reflected->getFileName());
    }

    /**
     * 转小写，将AbcDef => abc_def
     * @param $name
     * @param string $division
     * @return mixed
     */
    public function snakeName($name, $division = '_'){
        /*$format_str = preg_replace("/([A-Z])/", "_\\1", $name);
        $_name = trim($format_str, '_');
        return strtolower($_name);*/

        return Str::snake($name, $division);
    }

    /**
     * 转大写
     * @param $name
     * @return string
     */
    public function studlyName($name){
        return Str::studly($name);
    }

    /**从一个类中获取目录名当作名称
     * @param $class
     * @return mixed
     */
    public function getName($class = ''){
        if(empty($class)) $class = get_class($this);

        $class = str_replace('\\', '/', $class);
        $_class = dirname($class);
        $pos = strripos($_class, '\\');
        $name = $pos ? substr($_class, $pos+1) : $_class;

        return $this->snakeName($name);

        return $this->snakeName($name);
    }

    /**数组默认值
     * @param $param
     * @param array $default
     * @return null
     */
    protected function getArrDefault($param, $default = []){
        if(null === $param || !is_array($param)){
            return $default;
        }
        return $param;
    }

    /**获取类名称
     * @param $class
     * @return mixed
     */
    public function getClassName($class = ''){
        $class = get_class($class);
        $pos = strrpos($class, '\\');
        return (false === $pos) ? $class : substr($class, $pos+1);
    }

    /**获取类命名
     * @param $namespace
     * @return mixed
     */
    public function getNamespaceName($namespace = '')
    {
        if(empty($namespace)) $namespace = __NAMESPACE__;
        return class_basename($namespace);
    }


}