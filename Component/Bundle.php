<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Awen\Bundles\Contracts\BundleInterface;
use Awen\Bundles\Contracts\ModuleInterface;
use Awen\Bundles\Contracts\ServiceInterface;
use Awen\Bundles\Exceptions\ModuleException;
use Awen\Bundles\Exceptions\ServiceException;
use Awen\Bundles\Exceptions\ServiceNotFoundException;
use Awen\Bundles\Extensions\ToolExtend;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use phpDocumentor\Reflection\Types\Object_;

abstract class Bundle extends ToolExtend implements BundleInterface
{
    /**
     * 模块
     * @var array
     */
    protected $modules = [];

    /**
     * 服务
     * @var array
     */
    protected $services = [];

    /**
     * bundle名称
     * @var
     */
    protected $name;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * 路径
     * @var
     */
    protected $path;

    /**
     * 内核
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Application
     */
    private $app;
    
    public function __construct(Application $app, Kernel $kernel, $name)
    {
        $this->app = $app;
        $this->filesystem = $app['files'];
        $this->kernel = $kernel;
        $this->name = $name;
        $this->path = $this->getCurrentPath();
    }

    /**
     * 获取路径
     * @return mixed
     */
    public function getLoweName()
    {
        return $this->snakeName($this->name);
    }

    /**
     * 获取路径
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 初始化模块
     * @throws ModuleException
     */
    public function initializeModules(){
        $modules = $this->getArrDefault($this->registerModules(), []);
        foreach($modules as $module_class){
            if(!class_exists($module_class)){
                $err = [
                    'en' => "[{$module_class}] This module entrance file non existent!",
                    'zh' => "[{$module_class}] 这个 Module 入口文件不存在!"
                ];
                throw new ModelNotFoundException($err);
            }

            $_name = $this->getName($module_class);
            $module = new $module_class($this->app, $this, $_name, $this->getLoweName());
            if (!$module instanceof ModuleInterface) {
                $err = [
                    'en' => "[{$this->getClassName($module)}] This module entrance file must realize to 'ModuleInterface' interface!",
                    'zh' => "[{$this->getClassName($module)}] 这个 Module 入口文件必须实现 'ModuleInterface' 接口!"
                ];
                throw new ModuleException($err);
            }

            if(isset($this->modules[$_name])){
                $err = [
                    'en' => "[{$module_class}] Attempting to register two identical names of the module!",
                    'zh' => "[{$module_class}] 试图注册两个名称相同的 Module!"
                ];
                throw new ModuleException($err);
            }
            $module->initializeParam();

            $this->modules[$_name] = $module;
        }
    }

    /**
     * 初始化服务
     */
    public function initializeServices(){
        $class = $this->registerServices();
        if(empty($class)) return;

        if(!class_exists($class)){
            $err = [
                'en' => "[{$class}] This service non existent!",
                'zh' => "[{$class}] 这个服务类不存在!"
            ];
            throw new ServiceNotFoundException($err);
        }

        $services = $class::registerServices();
        foreach ($services as $name => $service){
            if (!isset($service['class']) || !isset($service['config'])) {
                $err = [
                    'en' => "[{$name}] Register service class related parameters have problems!",
                    'zh' => "[{$name}] 服务注册相关的参数有问题!"
                ];
                throw new ServiceException($err);
            }

            if(!class_exists($service['class'])){
                $err = [
                    'en' => "[{$service['class']}] This service class file non existent!",
                    'zh' => "[{$service['class']}] 这个服务类文件不存在!"
                ];
                throw new ServiceNotFoundException($err);
            }

            $key = $this->getName();
            $_name = $this->snakeName($name);
            $this->services[$key][$_name] = $service;
        }
    }

     /**
      * 获取所有模块
     * @return array
     */
    public function getModules(){
        return $this->modules;
    }

    /**
     * 获取所有EventFiles
     * @return array
     */
    public function getEventFiles(){
        $event_files = [];
        foreach ($this->modules as $module){
            $key = $this->getLoweName().'.'.$module->getLowerName();
            $event_files[$key] = $module->getRegisterParam('events');
        }
        return $event_files;
    }

    /**
     * 获取所有服务
     * @return array
     */
    public function getServices(){
        return $this->services;
    }

    /**
     * 创建一个服务
     * @param $b_name
     * @param $s_name
     * @return Service
     * @throws ServiceException
     * @throws ServiceNotFoundException
     */
    public function makeService($b_name, $s_name){
        $_b_name = $this->snakeName($b_name);
        $_s_name = $this->snakeName($s_name);

        if(!isset($this->services[$_b_name]) || !isset($this->services[$_b_name][$_s_name])){
            $err = [
                'en' => "[{$s_name}] Can't find this service in the [{$b_name}] Bundle!",
                'zh' => "[{$s_name}] 在 [{$b_name}] 的 Bundle 里找不到这个服务!"
            ];
            throw new ServiceNotFoundException($err);
        }

        $class = null;
        $config = null;
        $param = [
            'bundle_name' => $_b_name,
            'service_name' => $_s_name,
            'service_config' => []
        ];

        if(isset($this->services[$_b_name][$_s_name]['class']))
            $class = $this->services[$_b_name][$_s_name]['class'];

        if(isset($this->services[$_b_name][$_s_name]['config']))
            $config = $this->services[$_b_name][$_s_name]['config'];

        if(is_null($class)){
            $err = [
                'en' => "[{$class}] This service cannot be empty and must be a service class!",
                'zh' => "[{$class}] 此服务不能为空，必须是一个服务类!"
            ];
            throw new ServiceException($err);
        }

        /**
         * @var $service Service
         */
        $service = new $class();
        if (!$service instanceof ServiceInterface) {
            $err = [
                'en' => "[{$this->getClassName($service)}] This service entrance file must realize to 'ServiceInterface' interface!",
                'zh' => "[{$this->getClassName($service)}] 这个 Service 入口文件必须实现 'ServiceInterface' 接口!"
            ];
            throw new ServiceException($err);
        }

        if(!is_null($config)){
            if(!is_file($config) || !file_exists($config)){
                $err = [
                    'en' => "[{$config}] The configuration of this service file must be a valid file!",
                    'zh' => "[{$config}] 此服务的配置文件必须是一个有效的文件!"
                ];
                throw new ServiceException($err);
            }

            //处理配置文件
            $config_arr = require $config;
            if(!is_array($config_arr)){
                $err = [
                    'en' => "[{$config}] The configuration of this service must be a valid array!",
                    'zh' => "[{$config}] 此服务的配置必须是一个有效的文件!"
                ];
                throw new ServiceException($err);
            }


            $param['service_config'] = $this->getArrDefault($config_arr, []);
        }

        $service->setParam($param);
        $service->initializeClassFiles();

        return $service;
    }

    /**
     * 检测模块是否存在
     * @param $name
     * @return mixed
     */
    public function hasModule($name)
    {
        $_name = $this->snakeName($name);
        return array_key_exists($_name, $this->modules);
    }

    /**
     * 获取Module
     * @param $name
     * @return mixed|null
     */
    public function getModule($name){
        $_name = $this->snakeName($name);
        if (isset($this->modules[$_name])) {
            return $this->modules[$_name];
        }
        return null;
    }

    /**
     * 删除Module
     * @param $name
     * @return bool
     */
    public function deleteModule($name)
    {
        $_name = $this->snakeName($name);
        if (isset($this->modules[$_name])) {
            unset($this->modules[$_name]);
        }
        return true;
    }

    /**
     * 删除当前实体Bundle
     * @return bool
     */
    public function delete(){
        $this->kernel->deleteBundle($this->getName());
        return $this->filesystem->deleteDirectory($this->getPath(), true);
    }

    /**
     * 获取参数
     * @return array
     */
    public function getParam(){
        return [
            'path' => $this->path,
            'name' => $this->name,
        ];
    }

    /**
     * 获取参数
     * @param $name
     * @param $attr
     * @return bool
     */
    public function getModuleParam($name, $attr){
        $_name = $this->snakeName($name);
        if (isset($this->modules[$_name])) {
            return $this->modules[$_name]->getRegisterParam($attr);
        }
        return null;
    }
}