<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Awen\Bundles\Contracts\ServiceKernelInterface;
use Awen\Bundles\Exceptions\BundleException;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\View\View;

abstract class ServiceKernel implements ServiceKernelInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var Kernel
     */
    private $app_kernel;

    /**
     * @var Repository
     */
    private $config;

    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->config = $this->app['config'];
        $bootstrap = $this->app['bundles'];
        $this->app_kernel = $bootstrap->isBootKernel() ? $bootstrap->getKernel() : null ;
    }

    /**
     * 获取服务
     * 调用： 'bundleName.serviceName'
     * @param $name
     * @param $reset
     * @param null $default
     * @return mixed|Service
     * @throws BundleException
     */
    public function getService($name, $reset = false, $default = null){
        if(null === $this->app_kernel){
            $err = [
                'en' => "You did not initialize a bundle application,Unable to access service!",
                'zh' => "你没有初始化一个 Bundle 应用，不能获取服务!"
            ];
            throw new BundleException($err);
        }

        $service = $this->app_kernel->getService($name, $reset);
        if(null === $service && $default != null){
            return $this->app_kernel->getService($default, $reset);
        }

        return $service;
    }

    /**
     * 运行当前服务所在的Controller
     * @param $controller
     * @param array $param
     * @param string $cate
     * @return mixed
     */
    public function renderController($controller, array $param = [], $cate = 'view'){
        $con = explode('.', $controller);
        array_walk($con, function(&$v){$v = ucfirst($v);});
        $namespace = str_replace(':', 'Controller@', implode('\\',$con));

        //当前命名空间
        $service_name = [
            class_basename(get_class($this)),
            $this->config->get('bundles.root.generator.paths.service')
        ];
        $bundle = explode('\\', get_class($this));
        array_walk($bundle, function(&$v) use($service_name) {
            if(in_array($v, $service_name)){
                $v = '';
            }
        });
        $bundleNamespace = implode('\\', array_filter($bundle));

        //分类命名空间
        if($cate === 'view'){
            $appointNamespace = $this->config->get('bundles.root.generator.paths.view_controller');
        }else{
            $appointNamespace = $this->config->get('bundles.root.generator.paths.api_controller');
        }

        $controllerNamespace = $bundleNamespace. '\\'. str_replace('/', '\\', $appointNamespace).'\\' . $namespace;

        if(!is_null($this->app_kernel)){
            $result = $this->app_kernel->call($controllerNamespace, $param);
            if($result instanceof View){
                return $result->render();
            }else{
                return $result;
            }
        }

        return null;
    }

    /**
     * 获取Bundle名称
     * @param $bundle
     * @return mixed
     */
    public function getBundleName($bundle = null){
        if(is_null($bundle)) return $this->app_kernel->getCurrentBundle($this)->getName();

        return $this->app_kernel->getBundle($bundle)->getName();
    }

}