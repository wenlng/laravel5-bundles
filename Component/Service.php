<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Awen\Bundles\Contracts\ServiceInterface;
use Awen\Bundles\Exceptions\ServiceException;
use Awen\Bundles\Exceptions\ServiceNotFoundException;
use Awen\Bundles\Extensions\ToolExtend;
use Illuminate\Foundation\Application;

abstract class Service extends ToolExtend implements ServiceInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * 内核
     * @var Kernel
     */
    private $app_kernel;

    /**
     * 服务参数
     * @var array
     */
    private $params = [];

    /**
     * 服务名称
     * @var string
     */
    protected $service_name;

    /**
     * 包名称
     * @var string
     */
    protected $bundle_name;

    /**
     * 注册服务类文件
     * @var array
     */
    private $class_files = [];

    /**
     * 注册服务类实例
     * @var array
     */
    private $classes = [];

    /**
     * 如果两次相同，判定死递归
     * @var int
     */
    private $make_name = [];

    public function __construct()
    {
        $this->app = Application::getInstance();
        $bootstrap = $this->app['bundles'];
        $this->app_kernel = $bootstrap->isBootKernel() ? $bootstrap->getKernel() : null;
    }

	/**
     * 创建一个类
     * @param $class
     * @param bool $reset
     * @return mixed
     * @throws ServiceException
     * @throws ServiceNotFoundException
     */
    public function make($class, $reset = false){
        $_class = $this->snakeName($class);

        if (isset($this->classes[$_class]) && $reset) {
            return $this->classes[$_class];
        }

        if (!isset($this->class_files[$_class])) {
            $err = [
                'en' => "[{$class}] Class for this service does not exist!",
                'zh' => "[{$class}] 此服务类不存在!"
            ];
            throw new ServiceNotFoundException($err);
        }

        $service = $this->makeClass($this->class_files[$_class], $class);
        if (!is_null($service)) return $service;

        $err = [
            'en' => "[{$class}] Failed to perform service class!",
            'zh' => "[{$class}] 执行服务类失败!"
        ];
        throw new ServiceException($err);
    }
	
    /**
     * 执行实例
     *  调用： serviceClassName::serviceClassFunc
     * @param $func_name
     * @param $param
     * @return mixed
     * @throws ServiceException
     * @throws ServiceNotFoundException
     */
    public function execute($func_name, $param = [])
    {
        $class_func = explode('.', $func_name);
        if (count($class_func) != 2) {
            $err = [
                'en' => "[{$func_name}] The method of executing the service is in a format error. Please execute it in'ClassName.FuncName'format!",
                'zh' => "[{$func_name}] 执行服务类的方法格式错误，请以 'ClassName.FuncName!"
            ];
            throw new ServiceException($err);
        }

        list($class, $func) = $class_func;
        $_class = $this->snakeName($class);

        if (is_null($func)) {
            $err = [
                'en' => "[{$class}] Class execution method for incoming service!",
                'zh' => "[{$class}] 需要输入服务类执行方法!"
            ];
            throw new ServiceException($err);
        }

        if (isset($this->classes[$_class])) {
            if (!method_exists($this->classes[$_class], $func)) {
                $err = [
                    'en' => "[{$class}] Class execution method {$func} for service does not exist!",
                    'zh' => "[{$class}] 此服务类执行方法 {$func} 不存在!"
                ];
                throw new ServiceNotFoundException($err);
            }
			
			if(!empty($param))
				return call_user_func([$this->classes[$_class], $func], $param);
			return call_user_func([$this->classes[$_class], $func]);
        }

        if (!isset($this->class_files[$_class])) {
            $err = [
                'en' => "[{$class}] Class for this service does not exist!",
                'zh' => "[{$class}] 此服务类不存在!"
            ];
            throw new ServiceNotFoundException($err);
        }

        $service = $this->makeClass($this->class_files[$_class], $class);
        if (!is_null($service)) {
            if (!method_exists($service, $func)) {
                $err = [
                    'en' => "[{$class}] Class execution method {$func} for service does not exist!",
                    'zh' => "[{$class}] 此服务类执行方法 {$func} 不存在!"
                ];
                throw new ServiceNotFoundException($err);
            }

			if(!empty($param))
				return call_user_func([$service, $func], $param);
			
			return call_user_func([$service, $func]);
        }

		$err = [
            'en' => "[{$class}] Failed to perform service class!",
            'zh' => "[{$class}] 执行服务类失败!"
        ];
        throw new ServiceException($err);
    }

    /**
     * 获取参数
     * @param $key
     * @return mixed|null
     */
    public function getParam($key = null)
    {
        if(!is_null($key) && isset($this->params[$key])) return $this->params[$key];

        if(is_null($key)){
            return $this->params;
        }

        return null;
    }

    /**
     * 设置参数
     * @param $params
     */
    public function setParam($params)
    {
        $this->bundle_name = $params['bundle_name'];
        $this->service_name = $params['service_name'];
        $this->params = $params['service_config'];
    }

    /**
     * 初始化服务类文件
     */
    public function initializeClassFiles()
    {
        foreach ($this->getArrDefault($this->registerClassFiles(), []) as $name => $class_param) {
            if (!isset($class_param['class']) || !isset($class_param['param'])) {
                $err = [
                    'en' => "[{$name}] Register service class related parameters are problematic!",
                    'zh' => "[{$name}] 注册服务类相关的参数有问题!"
                ];
                throw new ServiceException($err);
            }

            if (preg_match('/^[0-9a-zA-Z\_]*$/', $name) <= 0) {
                $err = [
                    'en' => "[{$name}] The registration service is only '0-9,A-Z,A-Z,_' named.!",
                    'zh' => "[{$name}] 注册服务类名只能是 '0-9,a-z,A-Z,_' 命名组成!"
                ];
                throw new ServiceException($err);
            }

            $_name = $this->snakeName($name);
            foreach ($this->class_files as $key => $_class) {
                if ($_name == $key || $class_param['class'] == $_class['class']) {
                    $err = [
                        'en' => "[{$name}] Attempting to register two identical names of the service class!",
                        'zh' => "[{$name}] 试图注册两个名称相同的服务类!"
                    ];
                    throw new ServiceException($err);
                }
            }

            $this->class_files[$_name] = $class_param;
        }
    }

    /**
     * 默认值
     * @param $param
     * @param array $default
     * @return null
     */
    protected function getArrDefault($param, $default = [])
    {
        if (null === $param || !is_array($param)) {
            return $default;
        }
        return $param;
    }


    /**
     * 处理参数
     * @param array $params
     * @return array
     * @throws ServiceException
     * @throws ServiceNotFoundException
     */
    private function makeParam(array $params)
    {
        $new_params = [];
        foreach ($params as $key => $param) {
            if ('%' == substr($param, 0, 1) && '%' == substr($param, -1, 1)) {
                $name = trim($param, '%');
                if (!isset($this->params[$name])) {
                    $err = [
                        'en' => "[{$name}] This parameter is not configured in config!",
                        'zh' => "[{$name}] 这个参数在config里没有配置!"
                    ];
                    throw new ServiceException($err);
                }
                $new_params[$key] = $this->params[$name];
            } elseif ('@' == substr($param, 0, 1)) {
                $is_cache = ('@' == substr($param, -1, 1));
                $name = trim($param, '@');
                $_name = $this->snakeName($name);
                if (!isset($this->class_files[$_name])) {
                    $err = [
                        'en' => "[{$name}] The service class could not be found!",
                        'zh' => "[{$name}] 找不到这个服务类!"
                    ];
                    throw new ServiceNotFoundException($err);
                }

                $service = $this->makeClass($this->class_files[$_name], $name, !$is_cache);
                if (is_null($service)) {
                    $err = [
                        'en' => "[{$name}] Failed to create lazy service class!",
                        'zh' => "[{$name}] 创建依懒服务类失败!"
                    ];
                    throw new ServiceException($err);
                }

                $new_params[$key] = $service;
            }elseif('[param]' == $param){
                $new_params[$key] = $this->params;
            }else{
              $new_params[$key] = $param;
            }
        }
        return $new_params;
    }

    /**
     * 处理类
     * @param $class
     * @param $name
     * @param $cache
     * @return object
     * @throws ServiceException
     * @throws ServiceNotFoundException
     */
    private function makeClass($class, $name, $cache = true)
    {
        if (!isset($class['class']) || !isset($class['param'])) {
            $err = [
                'en' => "[{$class}] Register service class related parameters are problematic!",
                'zh' => "[{$class}] 注册服务类相关的参数有问题!"
            ];
            throw new ServiceException($err);
        }

        $_name = $this->snakeName($name);
        if (isset($this->classes[$_name]) && $cache) {
            return $this->classes[$_name];
        }

        if(isset($this->make_name[$_name]) && $this->make_name[$_name] > 0){
            $this->make_name[$_name] = $this->make_name[$_name] + 1;
            if($cache){
                $err = [
                    'en' => "[{$name}] There is a problem with this class parameter, Enter dead recursion!",
                    'zh' => "[{$name}] 这个类依赖注入的参数有问题，进入死递归了!"
                ];
                throw new ServiceException($err);
            }
        }else{
            $this->make_name[$_name] = 1;
        }

        if (!class_exists($class['class'])) {
            $err = [
                'en' => "[{$class['class']}] This class does not exist!",
                'zh' => "[{$class['class']}] 这个类不存在!"
            ];
            throw new ServiceNotFoundException($err);
        }

        $param = $this->getArrDefault($class['param'], []);
        if (!empty($param)) {
            $param = $this->makeParam($param);
        }

        $_class = new \ReflectionClass($class['class']);
        $re_class = $_class->newInstanceArgs($param);

        $cache && $this->classes[$_name] = $re_class;
        return $re_class;
    }
}