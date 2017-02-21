<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 14:42
 */

namespace Awen\Bundles;

use Illuminate\Support\ServiceProvider;

class BundlesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->mergeConfig();
        $this->registerBundles();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServices();
        $this->registerProviders();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('bundles');
    }

    /**
     * Setup stub path.
     */
    public function registerProviders()
    {
        $this->app->register(__NAMESPACE__.'\\Providers\\ConsoleServiceProvider');
        $this->app->bind('BundlesRepository', 'Awen\Bundles\Repositories\Repository');
    }

    /**
     * 注册所有模块
     */
    protected function registerBundles()
    {
        $this->app->register('Awen\Bundles\Providers\BootstrapServiceProvider');
    }

    /**
     * 注册提供商服务
     */
    protected function registerServices()
    {
        $this->app->singleton('bundles', function ($app) {
            $root_path = $app['config']->get('bundles.root.path');
            $kernel = $app['config']->get('bundles.root.kernel');
            return new BundlesBootstrap($app, $kernel, $root_path);
        });
    }

    /**
     * 合并包配置文件
     */
    protected function mergeConfig()
    {
        $config_path = __DIR__.'/src/config/config.php';
        $root_path = __DIR__.'/src/config/root.php';

        $this->mergeConfigFrom($config_path, 'bundles');
        $this->mergeConfigFrom($root_path, 'bundles.root');

        $this->publishes([
            $config_path => config_path('bundles.php')
        ], 'bundle');
    }

}
