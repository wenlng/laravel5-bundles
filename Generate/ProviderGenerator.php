<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-28
 * Time: 13:42
 */
namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class ProviderGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
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
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.provider');
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
     * 生成Provider文件
     */
    public function generateFiles()
    {
        $provider_file = $this->getPath(). '/' . $this->getName(). $this->provider_suffix . '.php';
        if(!$this->filesystem->exists($provider_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($provider_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($provider_file, $this->getStubContents('provider'));
        }
        $this->console->info("Created : {$provider_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasProvider($name)
    {
        $provider = $this->getPath() . '/' . $name . $this->provider_suffix .'.php';
        if (file_exists($provider)) return true;
        return false;
    }

    /**
     * 生成Provider文件
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
        $provider = $name. $this->provider_suffix;
        if ($this->hasProvider($name)) {
            $this->console->error("The Provider: [{$provider}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Provider <info>[{$provider}]</info> Created successfully in bundle: <info>[{$bundle_name}]</info>");
    }

    /**
     * 获取provider名称
     * @return string
     */
    protected function getProviderNameReplacement()
    {
        return $this->getName() . $this->provider_suffix;
    }

    /**
     * 获取provider命名
     * @return string
     */
    protected function getProviderNamespaceReplacement()
    {
        $provider = $this->rootConfig('generator.paths.provider', true);
        return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $provider);
    }

}