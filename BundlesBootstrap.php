<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles;

use Awen\Bundles\Component\Kernel;
use Awen\Bundles\Exceptions\BundleException;
use Awen\Bundles\Contracts\KernelInterface;
use Illuminate\Foundation\Application;

class BundlesBootstrap
{
    /**
     * Bundle根目录
     * @var string
     */
    protected $root_path;

    /**
     * @var Application
     */
    private $app;

    /**
     * 内核
     * @var Kernel
     */
    private $kernel;

    /**
     * 引导文件名称
     * @var string
     */
    protected $kernel_name;

    /**
     * 是否已初始化
     * @var bool
     */
    private $exist_kernel = false;

    public function __construct(Application $app, $kernel, $root_path = '/')
    {
        $this->app = $app;
        $this->root_path = $root_path;        
        $this->kernel_name = $kernel;
    }

    /**
     * 初始化内核
     * @param $path
     * @return mixed
     * @throws BundleException
     */
    public function initKernel($path)
    {
        if($this->exist_kernel) return;

        $app_kernel = $path . '/' . $this->kernel_name . '.php';
        if (!file_exists($app_kernel)) return ;

        require_once $app_kernel;
        $class = $this->kernel_name;

        if(!class_exists($class)) return ;

        $this->kernel = new $class($this->app, $this->root_path);
        if (!$this->kernel instanceof KernelInterface) {
            $err = [
                'en' => "[{$app_kernel}] This bundle kernel entrance file must realize to 'KernelInterface' interface",
                'zh' => "[{$app_kernel}] 这个 Bundle 内核入口文件必须实现 'KernelInterface' 这个接口"
            ];
            throw new BundleException($err);
        }

        $this->kernel->boot();
        $this->exist_kernel = true;
   }

    /**
     * 是否引导内核
     * @return bool
     */
    public function isBootKernel(){
        return $this->exist_kernel;
    }

    /**
     * 获取Bundle处理内核
     * @return Kernel|null
     */
    public function getKernel(){
        if($this->isBootKernel()){
            return $this->kernel;
        }
        return null;
    }

    /**
     * 注册模块
     */
    public function register()
    {
        if(!$this->exist_kernel) $this->initKernel($this->root_path);
        if($this->isBootKernel()){
            foreach ($this->kernel->getModules() as $module) {
                $module->register();
            }
        }
    }

    /**
     * 启动模块
     */
    public function boot()
    {
        if(!$this->exist_kernel) $this->initKernel($this->root_path);
        if($this->isBootKernel()){
            foreach ($this->kernel->getModules() as $module) {
                $module->boot();
            }
        }
    }

}