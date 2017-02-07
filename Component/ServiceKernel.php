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
use Illuminate\Foundation\Application;

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

    public function __construct()
    {
        $this->app = Application::getInstance();
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

}