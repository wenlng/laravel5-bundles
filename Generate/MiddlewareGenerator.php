<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class MiddlewareGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $cate = 'r';  // r/a/v


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
        if (!empty($cate) && (strtolower($cate) == 'a' || strtolower($cate) == 'v')) $this->cate = strtolower($cate);
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
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.api_middleware');
        elseif(strtolower($this->cate) == 'v')
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.view_middleware');
        else
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.middleware');
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
     * 替换文本中的变量
     * @param $stub
     * @param $str
     * @return mixed
     */
    protected function getStubStr($stub, $str)
    {
        foreach ($this->getReplacement($stub) as $search => $replace) {
            $str = str_replace('%' . strtoupper($search) . '%', $replace, $str);
        }
        return $str;
    }


    /**
     * 生成Middleware文件
     */
    public function generateFiles()
    {
        $middleware_file = $this->getPath(). '/' . $this->getName() . '.php';
        if(!$this->filesystem->exists($middleware_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($middleware_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($middleware_file, $this->getStubContents('middleware'));
        }
        $this->console->info("Created : {$middleware_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasMiddleware($name)
    {
        $middleware = $this->getPath() . '/' . $name . '.php';
        if (file_exists($middleware)) return true;
        return false;
    }


    /**
     * 生成Middleware文件
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
        if ($this->hasMiddleware($name)) {
            $this->console->error("The Middleware: [{$name}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Middleware <info>[{$name}]</info> created successfully in bundle: <info>[{$bundle_name}]</info>");
    }


    /**
     * 获取Middleware名称
     * @return string
     */
    protected function getMiddlewareNameReplacement()
    {
        return $this->getName();
    }

    /**
     * 获取Middleware命名
     * @return string
     */
    protected function getMiddlewareNamespaceReplacement()
    {
        if(strtolower($this->cate) == 'a')
            $middleware = $this->rootConfig('generator.paths.api_middleware');
        elseif(strtolower($this->cate) == 'v')
            $middleware = $this->rootConfig('generator.paths.view_middleware');
        else
            $middleware = $this->rootConfig('generator.paths.middleware');

        return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $middleware);
    }

}