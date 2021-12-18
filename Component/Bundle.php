<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Awen\Bundles\Contracts\BundleInterface;
use Awen\Bundles\Contracts\ServiceInterface;
use Awen\Bundles\Exceptions\BundleException;
use Awen\Bundles\Exceptions\ServiceException;
use Awen\Bundles\Exceptions\ServiceNotFoundException;
use Awen\Bundles\Extensions\ToolExtend;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Events\Dispatcher as Event;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Routing\Router;
use Illuminate\Translation\Translator;

abstract class Bundle extends ToolExtend implements BundleInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Repository
     */
    private $config;

    /**
     * 内核
     * @var Kernel
     */
    private $kernel;

    /**
     * 服务
     * @var array
     */
    protected $services = [];

    /**
     * 名称
     * @var string
     */
    protected $name;

    /**
     * 路径
     * @var string
     */
    protected $path;

    /**
     * 参数
     * @var array
     */
    protected $parameter = [];

    /**
     * 路由
     * @var array
     */
    protected $routes = [];

    /**
     * 别名
     * @var array
     */
    protected $aliases = [];

    /**
     * 服务提供
     * @var array
     */
    protected $providers = [];

    /**
     * 路由中间件
     * @var array
     */
    protected $route_middleware = [];

    /**
     * 中间件组
     * @var array
     */
    protected $groups_middleware = [];

    /**
     * 事件
     * @var array
     */
    protected $events = [];

    /**
     * 事件组
     * @var array
     */
    protected $subscribes = [];

    /**
     * 命令行
     * @var array
     */
    protected $consoles = [];


    public function __construct(Application $app, Kernel $kernel, $name)
    {
        $this->app = $app;
        $this->kernel = $kernel;
        $this->router = $this->app['router'];
        $this->event = $this->app['events'];
        $this->translator = $this->app['translator'];
        $this->filesystem = $app['files'];
        $this->config = $app['config'];
        $this->name = $name;
        $this->path = $this->getCurrentPath();
    }

    /**
     * 获取路径
     * @return mixed
     */
    public function getLowerName()
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
     * 初始化服务
     */
    public function initializeServices()
    {
        $class = $this->registerServices();
        if (empty($class)) return;

        if (!class_exists($class)) {
            $err = [
                'en' => "[{$class}] This service non existent!",
                'zh' => "[{$class}] 这个服务类不存在!"
            ];
            throw new ServiceNotFoundException($err);
        }

        $this->app->singleton($class);
        $services = $class::registerServices();
        foreach ($services as $name => $service) {
            if (!isset($service['class']) || !isset($service['config'])) {
                $err = [
                    'en' => "[{$name}] Register service class related parameters have problems!",
                    'zh' => "[{$name}] 服务注册相关的参数有问题!"
                ];
                throw new ServiceException($err);
            }

            if (!class_exists($service['class'])) {
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
     * 获取参数
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function getDefaultParam($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->parameter;
        }

        if (isset($this->parameter[$key]) && !empty($this->parameter[$key])) {
            return $this->parameter[$key];
        }
        return $default;
    }

    /**
     * 设置参数
     * @param array $params
     */
    final protected function setParam(array $params)
    {
        $this->parameter = array_merge($this->parameter, $params);
    }

    /**
     * 初始化参数
     */
    public function initializeParam()
    {
        $this->setParam($this->registerParams());
        $this->path = $this->getPath();
    }

    /**
     * 注册这个模块的别名
     */
    protected function registerAliases()
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->getArrDefault($this->registerClassAliases(), []) as $aliasName => $aliasClass) {
            if (array_key_exists($aliasName, $loader->getAliases())) {
                $err = [
                    'en' => "[{$aliasName}] Attempting to register two identical names of the alias!",
                    'zh' => "[{$aliasName}] 试图注册两个名称相同的别名!"
                ];
                throw new BundleException($err);
            }
            $loader->alias($aliasName, $aliasClass);
            $this->aliases[$aliasName] = $aliasClass;
        }
    }

    /**获取服务名
     * @param $name
     * @return mixed
     */
    private function getServiceProviderName($name)
    {
        $bas_name = str_replace('ServiceProvider', '', $name);
        return strtolower($bas_name);
    }

    /**
     * 注册这个模块的服务提供者
     */
    protected function registerProviders()
    {
        $namespace = $this->getDefaultParam('namespace', '');
        $bundle_provider = $namespace . '\\' . $this->getDefaultParam('provider', '');

        $this->app->register($bundle_provider);
        $name = $this->getServiceProviderName($this->getNamespaceName($bundle_provider));
        $this->providers[$name] = $bundle_provider;

        foreach ($this->getArrDefault($this->registerProviderFiles(), []) as $provider) {
            $this->app->register($provider);
            $key = $this->getServiceProviderName($this->getNamespaceName($provider));
            $this->providers[$key] = $provider;
        }
    }

    /**
     * 注册这个模块的路由文件
     */
    protected function registerRoute()
    {
        foreach ($this->getArrDefault($this->registerRouteFiles(), []) as $name => $route) {
            if (!isset($route['route_file']) || !is_file($route['route_file']) || !isset($route['route_file'])) {
                $err = [
                    'en' => "[{$name}] There is a problem with this routing configuration, please check!",
                    'zh' => "[{$name}] 此路由配置有问题，请检查!"
                ];
                throw new BundleException($err);
            }

            $this->router->group($this->getArrDefault($route['group_params'], []), function ($router) use ($route) {
                require $route['route_file'];
            });

            $this->routes[$name] = $route;
        }
    }

    /**
     * 注册中间件
     */
    protected function registerMiddleware()
    {
        $middleware = $this->registerMiddlewareFiles();
        foreach ($this->getArrDefault($middleware['route'], []) as $name => $class) {
            $this->router->middleware($name, $class);
            $this->route_middleware[$name] = $class;
        }

        foreach ($this->getArrDefault($middleware['groups'], []) as $name => $class) {
            if ('web' == $name || 'api' == $name) {
                if (is_array($class)) {
                    foreach ($class as $sub_class) {
                        $this->router->pushMiddlewareToGroup($name, $sub_class);
                        $this->groups_middleware[$name][] = $sub_class;
                    }
                } else {
                    $this->router->pushMiddlewareToGroup($name, $class);
                    $this->groups_middleware[$name] = $class;
                }
            } else {
                $this->router->middlewareGroup($name, $class);
                $this->groups_middleware[$name] = $class;
            }
        }
    }

    /**
     * 注册中间件
     */
    protected function registerEvents()
    {
        foreach ($this->getArrDefault($this->registerEventFiles(), []) as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->event->listen($event, $listener);
            }
            $this->events[$event] = $listeners;
        }
    }

    /**
     * 注册Subscribe
     */
    protected function registerSubscribes()
    {
        foreach ($this->getArrDefault($this->registerSubscribeFiles(), []) as $subscriber) {
            $this->event->subscribe($subscriber);
            $this->subscribes[] = $subscriber;
        }
    }

    /**获取命令名
     * @param $name
     * @return mixed
     */
    private function getCommandName($name)
    {
        $bas_name = str_replace('Command', '', $name);
        return strtolower($bas_name);
    }

    /**
     * 注册命令行
     */
    protected function registerConsoles()
    {
        foreach ($this->getArrDefault($this->registerConsoleFiles(), []) as $command) {
            $this->event->listen(ArtisanStarting::class, function ($event) use ($command) {
                $event->artisan->resolveCommands($command);

            });
            $name = $this->getCommandName($this->getNamespaceName($command));
            $this->consoles[$name] = $command;
        }
    }

    /**
     * 注册模块相关
     */
    public function register()
    {
        $this->registerAliases();
        $this->registerProviders();
        $this->registerRoute();
        $this->registerMiddleware();
        $this->registerEvents();
        $this->registerSubscribes();
        $this->registerConsoles();
    }

    /**
     * 注册模块的语言翻译
     */
    protected function registerTranslation()
    {
        $lowerName = $this->getLowerName();
        $langPath = base_path("resources/lang/{$lowerName}");

        if (is_dir($langPath)) {
            $this->translator->addNamespace($langPath, $lowerName);
        }
    }

    /**
     * 引导应用程序事件
     */
    public function boot()
    {
        $this->registerTranslation();
    }


    /**
     * 获取当前相关参数
     * @param string $name
     * @return array
     */
    public function getRegisterParam($name)
    {
        $public_param = [
            'path',
            'name',
            'parameter',
            'routes',
            'aliases',
            'providers',
            'route_middleware',
            'groups_middleware',
            'events',
            'subscribes',
            'consoles',
        ];

        if (!property_exists($this, $name) || !in_array($name, $public_param)) {
            return [];
        }

        return $this->$name;
    }

    /**
     * 获取所有EventFiles
     * @return array
     */
    public function getEventFiles()
    {
        return $this->getRegisterParam('events');
    }

    /**
     * 获取所有服务
     * @return array
     */
    public function getServices()
    {
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
    public function makeService($b_name, $s_name)
    {
        $_b_name = $this->snakeName($b_name);
        $_s_name = $this->snakeName($s_name);

        if (!isset($this->services[$_b_name]) || !isset($this->services[$_b_name][$_s_name])) {
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

        if (isset($this->services[$_b_name][$_s_name]['class']))
            $class = $this->services[$_b_name][$_s_name]['class'];

        if (isset($this->services[$_b_name][$_s_name]['config']))
            $config = $this->services[$_b_name][$_s_name]['config'];

        if (is_null($class)) {
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

        if (!is_null($config)) {
            if (!is_file($config) || !file_exists($config)) {
                $err = [
                    'en' => "[{$config}] The configuration of this service file must be a valid file!",
                    'zh' => "[{$config}] 此服务的配置文件必须是一个有效的文件!"
                ];
                throw new ServiceException($err);
            }

            //处理配置文件
            $config_arr = require $config;
            if (!is_array($config_arr)) {
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
     * 删除当前实体Bundle
     * @return bool
     */
    public function delete()
    {
        $this->kernel->deleteBundle($this->getName());
        return $this->filesystem->deleteDirectory($this->getPath(), true);
    }

    /**
     * 获取参数
     * @return array
     */
    public function getParam()
    {
        return [
            'path' => $this->path,
            'name' => $this->name,
        ];
    }

    /**
     * 获取模块storage路径
     * @param $name
     * @return string|null
     */
    public function getStoragePath($name = '')
    {
        $storage_path = $this->config->get('bundles.paths.storage') . '/' . $this->getLowerName();
        if (!is_dir($storage_path)) $this->filesystem->makeDirectory($storage_path, 0755, true);

        return $storage_path . '/' . $name;
    }

    /**
     * 获取模块asset路径
     * @param $name
     * @return string|null
     */
    public function getAssetUrl($name = '')
    {
        $storage_path = str_replace(public_path(), '', $this->config->get('bundles.paths.assets')) . '/' . $this->getLowerName() . '/' . $name;

        return asset(trim($storage_path, '\,/'));
    }
}