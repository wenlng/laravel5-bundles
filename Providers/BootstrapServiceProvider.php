<?php
/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 12:47
 */

namespace Awen\Bundles\Providers;

use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
    /**
     * 启动包
     */
    public function boot()
    {
        $this->app['bundles']->boot();
    }

    /**
     * 注册服务提供者
     */
    public function register()
    {
        $this->app['bundles']->register();
    }
}