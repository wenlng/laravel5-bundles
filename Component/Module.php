<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Awen\Bundles\Contracts\ModuleInterface;
use Awen\Bundles\Exceptions\ModuleException;
use Awen\Bundles\Extensions\ToolExtend;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Events\Dispatcher as Event;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Routing\Router;
use Illuminate\Translation\Translator;

abstract class Module extends ToolExtend implements ModuleInterface
{
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
     * @var Application
     */
    private $app;

    /**
     * 内核
     * @var Bundle
     */
    private $bundle;

    /**
     * @var Filesystem
     */
    private $filesystem;

    protected $path;
    protected $name;
    protected $bundle_name;
    protected $parameter = [];
    protected $routes = [];
    protected $aliases = [];
    protected $providers = [];
    protected $route_middleware = [];
    protected $groups_middleware = [];
    protected $events = [];
    protected $subscribes = [];
    protected $consoles = [];

    public function __construct(Application $app, Bundle $bundle, $module_name, $bundle_name)
    {
        $this->app = $app;
        $this->bundle = $bundle;
        $this->name = $module_name;
        $this->bundle_name = $bundle_name;
        $this->router = $this->app['router'];
        $this->event = $this->app['events'];
        $this->translator = $this->app['translator'];
        $this->filesystem = $this->app['files'];
        $this->path = $this->getCurrentPath();
    }

    /**
     * 获取小写名称
     * @return string
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
            if(array_key_exists($aliasName, $loader->getAliases())){
                $err = [
                    'en' => "[{$aliasName}] Attempting to register two identical names of the alias!",
                    'zh' => "[{$aliasName}] 试图注册两个名称相同的别名!"
                ];
                throw new ModuleException($err);
            }
            $loader->alias($aliasName, $aliasClass);
            $this->aliases[$aliasName] = $aliasClass;
        }
    }

    /**获取服务名
     * @param $name
     * @return mixed
     */
    private function getServiceProviderName($name){
        $bas_name = str_replace('ServiceProvider', '', $name);
        return strtolower($bas_name);
    }

    /**
     * 注册这个模块的服务提供者
     */
    protected function registerProviders()
    {
        $namespace = $this->getDefaultParam('module_namespace', '');
        $module_provider = $namespace. '\\' . $this->getDefaultParam('module_provider', '');

        $this->app->register($module_provider);
        $name = $this->getServiceProviderName($this->getNamespaceName($module_provider));
        $this->providers[$name] = $module_provider;

        foreach ($this->getArrDefault($this->registerProviderFiles(), []) as $name) {
            $provider = $namespace. '\\' .$name;
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
            if(!isset($route['route_file']) || !is_file($route['route_file']) || !isset($route['route_file'])){
                $err = [
                    'en' => "[{$name}] There is a problem with this routing configuration, please check!",
                    'zh' => "[{$name}] 此路由配置有问题，请检查!"
                ];
                throw new ModuleException($err);
            }

            $this->router->group($this->getArrDefault($route['group_params'],[]), function ($router) use ($route) {
                require $route['route_file'];
            });

            //require $route['route_file'];
            $this->routes[$name] = $route;
        }
    }

    /**
     * 注册中间件
     */
    protected function registerMiddleware(){
        $middleware = $this->registerMiddlewareFiles();
        $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];

        foreach ($this->getArrDefault($middleware['route'],[]) as $name => $class) {
            $this->router->middleware($name, $class);
            $this->route_middleware[$name] = $class;
        }

        foreach ($this->getArrDefault($middleware['groups'], []) as $name => $class)
		{
            if('web' == $name || 'api' == $name){
                if (is_array($class)){
                    foreach ($class as $sub_class){
                        $kernel->pushMiddleware($sub_class);

                        $this->router->pushMiddlewareToGroup($name, $sub_class);
                        $this->groups_middleware[$name][] = $sub_class;
                    }
                }else{
                    $kernel->pushMiddleware($class);

                    $this->router->pushMiddlewareToGroup($name, $class);
                    $this->groups_middleware[$name] = $class;
                }
            }else{
                $this->router->middlewareGroup($name, $class);
                $this->groups_middleware[$name] = $class;
            }
        }
    }

    /**
     * 注册中间件
     */
    protected function registerEvents(){
        foreach ($this->getArrDefault($this->registerEventFiles(),[]) as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->event->listen($event, $listener);
                $this->events[$event] = $listener;
            }
        }
    }

    /**
     * 注册Subscribe
     */
    protected function registerSubscribes(){
        foreach ($this->getArrDefault($this->registerSubscribeFiles(),[]) as $subscriber) {
            $this->event->subscribe($subscriber);
            $this->subscribes[] = $subscriber;
        }
    }
    /**获取命令名
     * @param $name
     * @return mixed
     */
    private function getCommandName($name){
        $bas_name = str_replace('Command', '', $name);
        return strtolower($bas_name);
    }

    /**
     * 注册命令行
     */
    protected function registerConsoles(){
        foreach ($this->getArrDefault($this->registerConsoleFiles(),[]) as $command) {
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
     * 删除当前实体Module
     * @return bool
     */
    public function delete(){
        $this->bundle->deleteModule($this->getName());
        return $this->filesystem->deleteDirectory($this->getPath(), true);
    }

    /**
     * 获取当前相关参数
     * @param string $name
     * @return array
     */
    public function getRegisterParam($name){
        $public_param = [
            'path',
            'name',
            'bundle_name',
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

        if(!property_exists($this, $name) || !in_array($name, $public_param)){
           return [];
        }

        return $this->$name;
    }

    /**
     * 获取当前key
     * @return string
     */
    public function getModuleKey(){
        return $this->bundle_name . '.' . $this->getLowerName();
    }
}