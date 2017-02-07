<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class RequestGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $cate = 'v';


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
        $module_path = $this->getModulePath();
        if (strtolower($this->cate) == 'a')
            $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.api_request');
        else
            $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.view_request');

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
        $replacements = $this->rootConfig('modules.replacements');
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
     * 生成Request文件
     */
    public function generateFiles()
    {
        $request_file = $this->getPath() . '/' . $this->getName() . $this->request_suffix . '.php';
        if (!$this->filesystem->exists($request_file)) {
            if (!$this->filesystem->isDirectory($dir = dirname($request_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($request_file, $this->getStubContents('request'));
        }
        $this->console->info("Created : [{$request_file}]");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRequest($name)
    {
        $request = $this->getPath() . '/' . $name . $this->request_suffix . '.php';
        if (file_exists($request)) return true;
        return false;
    }


    /**
     * 生成Request文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        $module_name = $this->getModuleName();
        if(!$this->hasBundleOrModule($bundle_name, $module_name)) return;

        $name = $this->getName();
        $request = $name . $this->request_suffix;
        if ($this->hasRequest($name)) {
            $this->console->error("The Request: [{$request}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Request <info>[{$request}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> to module: <info>[{$module_name}]</info> ");
    }


    /**
     * 获取Request名称
     * @return string
     */
    protected function getRequestNameReplacement()
    {
        return $this->getName() . $this->request_suffix;
    }

    /**
     * 获取Request命名
     * @return string
     */
    protected function getRequestNamespaceReplacement()
    {
        if (strtolower($this->cate) == 'a')
            $request = $this->rootConfig('modules.generator.paths.api_request');
        else
            $request = $this->rootConfig('modules.generator.paths.view_request');

        return $this->getModuleCurrentNamespace() . '\\' . str_replace('/', '\\', $request);
    }

}