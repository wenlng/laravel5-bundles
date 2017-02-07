<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-28
 * Time: 13:31
 */

namespace Awen\Bundles\Routing;

use Awen\Bundles\Component\Kernel;
use Awen\Bundles\Component\Service;
use Awen\Bundles\Contracts\ControllerInterface;
use Awen\Bundles\Exceptions\BundleException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Application;

class Controller extends BaseController implements ControllerInterface
{      
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Kernel
     */
    private $app_kernel;

    /**
     *
     */
    private $is_init = false;

    /**
     * 初始化
     */
    private function init()
    {
        if($this->is_init) return;

        if(null === $this->app){
            $this->app = Application::getInstance();
        }

        if(null === $this->app_kernel){
            $bootstrap = $this->app['bundles'];
            $this->app_kernel = $bootstrap->isBootKernel() ? $bootstrap->getKernel() : null ;
        }
    }

    /**
     * 获取当前Bundle的Service服务，
     *  调用： 'bundleName.serviceName'
     * @param $service
     * @param $reset
     * @param $default
     * @return mixed|Service
     * @throws BundleException
     */
    public function getService($service = null, $reset = false , $default = null){
        if(!$this->is_init) $this->init();

        if(null === $this->app_kernel){
            $err = [
                'en' => "You did not initialize a bundle application,Unable to access service!",
                'zh' => "你没有初始化一个 Bundle 应用，不能获取服务!"
            ];
            throw new BundleException($err);
        }

        $service = $this->app_kernel->getService($service, $reset);
        if(null === $service && $default != null){
            return $this->app_kernel->getService($default, $reset);
        }

        return $service;
    }

    /**
     * 获取包
     * @param null $bundle
     * @return array|mixed|null
     */
    public function getBundle($bundle = null){
        if(!$this->is_init) $this->init();
        if(!is_null($bundle))
            return $this->app_kernel->getBundle($bundle);
        else
            return $this->app_kernel->getBundles();
    }

    /**
     * 获取模块
     * @param null $bundle
     * @param null $module
     * @return array|mixed|null
     */
    public function getModule($bundle = null, $module = null){
        if(!$this->is_init) $this->init();

        if(!is_null($bundle) || !is_null($module))
            return $this->app_kernel->getModule($bundle, $module);
        else
            return $this->app_kernel->getModules();
    }

    /**
     * 获取当前包
     * @return mixed|null
     */
    public function getCurrentBundle(){
        if(!$this->is_init) $this->init();
        return $this->app_kernel->getCurrentBundle($this);
    }

    /**
     * 获取当前模块
     * @return mixed|null
     */
    public function getCurrentModule(){
        if(!$this->is_init) $this->init();
        return $this->app_kernel->getCurrentModule($this);
    }

    /**
     * 获取当前处理包的使用时间
     * @return string|null
     */
    public function getUseTime(){
        if(!$this->is_init) $this->init();
        return $this->app_kernel->getUseTime();
    }

    /**
     * 获取模块storage路径  getStoragePathPrefix
     * @param $name
     * @return string|null
     */
    public function getSPP($name){
        if(!$this->is_init) $this->init();
        return $this->app_kernel->getStoragePath($name);
    }

    /**
     * 获取模块asset路径  getAssetUrlPrefix
     * @param $name
     * @return string|null
     */
    public function getAUP($name){
        if(!$this->is_init) $this->init();
        return $this->app_kernel->getAssetUrl($name);
    }

    /**
     * 获取包相关参数
     * @param $bundle
     * @return array|null
     */
    public function getBundleParam($bundle){
        if(!$this->is_init) $this->init();

        return $this->app_kernel->getBundleParam($bundle);
    }

    /**
     * 检查Bundle是否存在
     * @param $bundle
     * @return mixed
     */
    public function hasBundle($bundle)
    {
        if(!$this->is_init) $this->init();

        return $this->app_kernel->hasBundle($bundle);
    }

    /**
     * 检查模块是否存在
     * @param $bundle
     * @param $module
     * @return mixed
     */
    public function hasModule($bundle, $module){
        if(!$this->is_init) $this->init();

        return $this->app_kernel->hasModule($bundle, $module);
    }

    /**
     * 获取模块参数
     * @param $bundle
     * @param $module
     * @return array|null
     */
    public function getModuleParam($bundle, $module){
        if(!$this->is_init) $this->init();

        return $this->app_kernel->getModuleParam($bundle, $module);
    }

    /**
     * 获取模块相关参数
     * @param $bundle
     * @param $module
     * @param $name
     *  $name = path、name、bundle_name、parameter、routes、aliases、providers、route_middleware、groups_middleware、events、subscribes、consoles
     * @return mixed|null
     */
    public function getModuleRegisterParam($bundle, $module, $name){
        if(!$this->is_init) $this->init();

        return $this->app_kernel->getModuleRegisterParam($bundle, $module, $name);
    }
}
