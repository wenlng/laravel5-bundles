<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Repositories;

use Awen\Bundles\Contracts\ResourcesRepositoryInterface;
use Awen\Bundles\Component\Kernel;
use Awen\Bundles\Supports\Migrate;
use Awen\Bundles\Supports\NameParser;
use Awen\Bundles\Supports\SchemaParser;
use Awen\Bundles\Supports\Stub;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

abstract class ResourcesRepository implements ResourcesRepositoryInterface
{
    /**
     * The Laravel application instance.
     * @var Application
     */
    protected $app;

    /**
     * The laravel config instance.
     * @var Config
     */
    protected $config;

    /**
     * The laravel filesystem instance.
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The laravel console instance.
     * @var Console
     */
    protected $console;

    /**
     * 内核.
     * @var Kernel
     */
    protected $app_kernel;

    /**
     * @var string
     */
    protected $bundle;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $bundle_suffix = 'Bundle';

    /**
     * @var string
     */
    protected $module_suffix = 'Module';

    /**
     * @var string
     */
    protected $controller_suffix = 'Controller';

    /**
     * @var string
     */
    protected $command_suffix = 'Command';

    /**
     * @var string
     */
    protected $model_suffix = 'Model';

    /**
     * @var string
     */
    protected $repository_suffix = 'Repository';

    /**
     * @var string
     */
    protected $provider_suffix = 'Provider';

    /**
     * @var string
     */
    protected $request_suffix = 'Request';

    /**
     * @var string
     */
    protected $job_suffix = 'Job';

    /**
     * @var string
     */
    protected $event_suffix = 'Event';

    /**
     * @var string
     */
    protected $listener_suffix = 'Listener';

    /**
     * @var string
     */
    protected $service_suffix = 'Service';


    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->filesystem = $this->app['files'];
        $this->config = $this->app['config'];

        Stub::setBasePath($this->config('root.paths.stub'));
        $bootstrap = $this->app['bundles'];
        $this->app_kernel = $bootstrap->isBootKernel() ? $bootstrap->getKernel() : null;
    }


    /**
     * 获取app
     * @return Application
     */
    public function getApp(){
        return $this->app;
    }

    /**
     * 设置Console
     * @param $console
     * @return $this
     */
    public function setConsole($console)
    {
        $this->console = $console;
        return $this;
    }

    /**
     * 获取Console
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * 获取Filesystem
     * @return Filesystem|mixed
     */
    public function getFilesystem(){
        return $this->filesystem;
    }
    /**
     * 创建一个Migrate
     * @param $path
     * @return Migrate
     */
    public function createMigrate($path)
    {
        return new Migrate($this->getApp(), $path);
    }

    /**
     * 创建一个SchemaParser
     * @param $schema
     * @return SchemaParser
     */
    public function createSchemaParser($schema)
    {
        return new SchemaParser($schema);
    }

    /**
     * 创建一个NameParser
     * @param $name
     * @return NameParser
     */
    public function createNameParser($name)
    {
        return new NameParser($name);
    }

    /**
     * 创建一个Stub
     * @param $path
     * @param array $replaces
     * @return Stub
     */
    public function createStub($path, array $replaces = [])
    {
        return new Stub($path, $replaces);
    }

    /**
     * 配置
     * @param $key
     * @return mixed
     */
    public function config($key)
    {
        return $this->config->get('bundles.' . $key);
    }

    /**
     * 配置
     * @param $key
     * @return mixed
     */
    public function rootConfig($key)
    {
        return $this->config('root.' . $key);
    }

    /**
     * 获取资module源路径
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    public function getAssetsPath($bundle_name, $module_name){
        return  $this->getModuleNamePath($bundle_name, $module_name) .'/'.$this->rootConfig('modules.generator.paths.assets');
    }

    /**
     * @param $bundle
     * @param $module
     * @return string
     */
    public function assetPath($bundle, $module)
    {
        return $this->config('paths.assets').'/'. Str::snake($bundle, '_').'/'.Str::snake($module, '_');
    }

    /**
     * 获取资Lang源路径
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    public function getLangPath($bundle_name, $module_name){
        return  $this->getModuleNamePath($bundle_name, $module_name) .'/'.$this->rootConfig('modules.generator.paths.lang');
    }

    /**
     * @param $bundle
     * @param $module
     * @return string
     */
    public function langPath($bundle, $module)
    {
        return $this->rootConfig('modules.generator.paths.lang').'/'. Str::snake($bundle, '_').'/'.Str::snake($module, '_');
    }

    /**
     * 获取资Migration源路径
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    public function getMigrationPath($bundle_name, $module_name){
        return  $this->getModuleNamePath($bundle_name, $module_name) .'/'.$this->rootConfig('modules.generator.paths.migration');
    }

    /**
     * @param $bundle
     * @param $module
     * @return string
     */
    public function migrationPath($bundle, $module)
    {
        return $this->config('paths.migration').'/'. Str::snake($bundle, '_').'/'.Str::snake($module, '_');
    }


    //===============================================

    /**
     * 设置包
     * @param $bundle
     * @return $this
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
        return $this;
    }


    /**
     * 转大写规范
     * @return string
     */
    public function getBundleName()
    {
        return Str::studly($this->bundle);
    }

    /**
     * 获取小写名字
     * @return mixed
     */
    public function getLowerBundleName()
    {
        return Str::snake($this->bundle, '_');
    }

    /**
     * 获取Bundle路径
     * @return mixed
     */
    public function getBundleRootPath(){
        return $this->rootConfig('path');
    }

    /**
     * 获取Bundle路径
     * @return string
     */
    public function getBundlePath(){
        $name = $this->getLowerBundleName();
        if (null !== $this->app_kernel && $this->app_kernel->hasBundle($name)) {
            return $this->app_kernel->getBundle($name)->getPath();
        }
        $path = $this->getBundleRootPath() . '/' . $this->getBundleName();
        return str_replace('\\', '/', $path);
    }

    /**
     * 获取Bundle路径
     * @param $name
     * @return string
     */
    public function getBundleNamePath($name){
        if (null !== $this->app_kernel && $this->app_kernel->hasBundle($name)) {
            return $this->app_kernel->getBundle($name)->getPath();
        }
        return $this->getBundleRootPath() . '/' . Str::studly($name);
    }

    /**
     * 检查Bundle目录是否已经存在Bundle
     * @return bool
     */
    public function hasBundle(){
        if(empty($this->bundle)) return false;

        $bundle = $this->getBundlePath();

        if(file_exists($bundle)) return true;

        if($this->hasBundleRegister()) true;

        return false;
    }

    /**
     * 检查Bundle是否已经注册Bundle
     * @return bool
     */
    public function hasBundleRegister(){
        if(empty($this->bundle)) return false;

        if(null !== $this->app_kernel && $this->app_kernel->hasBundle($this->getBundleName())) return true;

        return false;
    }

    //===============================================

    /**
     * 设置模块
     * @param $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }


    /**
     * 转大写规范
     * @return string
     */
    public function getModuleName()
    {
        return Str::studly($this->module);
    }

    /**
     * 获取小写名字
     * @return mixed
     */
    public function getLowerModuleName()
    {
        return Str::snake($this->module, '_');
    }

    /**
     * 获取包目录
     * @return string
     */
    public function getModulePath()
    {
        $name = $this->getLowerModuleName();
        if (null !== $this->app_kernel && $this->app_kernel->hasModule($this->getBundleName(), $name)) {
            return $this->app_kernel->getModule($this->getBundleName(), $name)->getPath();
        }
        $path = $this->getBundlePath().'/' . $this->rootConfig('bundles.generator.paths.module'). '/' . $this->getModuleName();
        return str_replace('\\', '/', $path);
    }

    /**
     * 获取包目录
     * @param $bundle
     * @param $name
     * @return string
     */
    public function getModuleNamePath($bundle, $name)
    {
        if (null !== $this->app_kernel && $this->app_kernel->hasModule($this->getBundleName(), $name)) {
            return $this->app_kernel->getModule($this->getBundleName(), $name)->getPath();
        }

        return $this->getBundleNamePath($bundle).'/' . $this->rootConfig('bundles.generator.paths.module'). '/' . Str::studly($name);
    }

    /**
     * 检查指定Bundle是否已经存在Module
     * @return bool
     */
    public function hasModule(){
        if(empty($this->module)) return false;

        $module = $this->getModulePath();

        if(is_dir($module)) return true;

        if($this->hasModuleRegister()) return true;

        return false;
    }

    /**
     * 检查Bundle是否已经注册Module
     * @return bool
     */
    public function hasModuleRegister(){
        if(empty($this->module)) return false;

        if(null !== $this->app_kernel && $this->app_kernel->hasModule($this->getBundleName(), $this->getModuleName())) return true;

        return false;
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

}