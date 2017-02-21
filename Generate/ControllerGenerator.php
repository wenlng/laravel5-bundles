<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class ControllerGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $cate = 'v';

    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $extend;


    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    /**
     * 设置分类
     * @param $cate
     * @return $this
     */
    public function setCate($cate)
    {
        if (!empty($cate) && strtolower($cate) == 'a') $this->cate = strtolower($cate);
        return $this;
    }

    /**
     * 设置分类
     * @param $path
     * @return $this
     */
    public function setPathSuffix($path)
    {
        if (!empty($path) ) $this->path = $path;
        return $this;
    }

    /**
     * 设置分类
     * @param $extend
     * @return $this
     */
    public function setExtend($extend)
    {
        if (!empty($extend)) $this->extend = $extend;
        return $this;
    }
    /**
     * 转大写规范
     * @return string
     */
    public function getName()
    {
        return Str::studly($this->name);
    }

    /**
     * 获取小写名字
     * @return mixed
     */
    public function getLowerName()
    {
        return Str::snake($this->name, '_');
    }

    /**
     * 获取目录
     * @return mixed
     */
    public function getPath()
    {
        $bundle_path = $this->getBundlePath();
        if(strtolower($this->cate) == 'a')
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.api_controller');
        else
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.view_controller');

        if(!empty($this->path)) $path = $path. '/' . trim($this->path, '\,/');

        return str_replace('\\', '/', $path);
    }

    /**
     * 获取模板内容，并替换内容中的变量
     * @param $stub
     * @return string
     */
    protected function getStubContents($stub)
    {
        return $this->createStub('/' . $stub . '.stub', $this->getReplacement($stub))->render();
    }

    /**查找将要替换的值
     * @param $stub
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->rootConfig('replacements');
        return $this->_getReplacement($stub, $replacements);
    }

    /**
     * 生成Controller文件
     */
    public function generateFiles()
    {
        $controller_file = $this->getPath(). '/' . $this->getName() . $this->controller_suffix . '.php';
        $key = 'controller';
        if(!$this->filesystem->exists($controller_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($controller_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            if(!empty($this->extend))$key = 'extend_controller';

            $this->filesystem->put($controller_file, $this->getStubContents($key));
        }
        $this->console->info("Created : {$controller_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasController($name)
    {
        $controller = $this->getPath() . '/' . $name . $this->controller_suffix . '.php';
        if (file_exists($controller)) return true;
        return false;
    }


    /**
     * 生成Controller文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        if(empty($bundle_name)){
            $this->console->error("Please appoint the bundle: -b BundleName!");
            return ;
        }
        if (!$this->hasBundle()) {
            $this->console->error("The bundle: [{$bundle_name}] not exist!");
            return ;
        }

        $name = $this->getName();
        $controller = $name.$this->controller_suffix;
        if ($this->hasController($name)) {
            $this->console->error("The Controller: [{$controller}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Controller <info>[{$controller}]</info> created successfully in bundle: <info>[{$bundle_name}]</info>");
    }

    /**
     * 获取Controller名称
     * @return string
     */
    protected function getControllerNameReplacement()
    {
        return $this->getName() . $this->controller_suffix;
    }

    /**
     * 获取Controller命名
     * @return string
     */
    protected function getControllerNamespaceReplacement()
    {
        if(strtolower($this->cate) == 'a')
            $controller = $this->rootConfig('generator.paths.api_controller', true);
        else
            $controller = $this->rootConfig('generator.paths.view_controller', true);

        if(!empty($this->path)) $controller = $controller . '\\' . trim(Str::studly($this->path), '\,/');

        return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $controller);
    }


    /**
     * 获取ControllerExtend名称
     * @return string
     */
    protected function getControllerExtendNameReplacement()
    {
        return Str::studly(class_basename($this->extend));
    }

    /**
     * 获取ControllerExtend命名
     * @return string
     */
    protected function getControllerExtendNamespaceReplacement()
    {
        return Str::studly(str_replace('/', '\\', $this->extend));
    }
}