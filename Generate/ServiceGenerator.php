<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class ServiceGenerator extends Generator
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
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.service') .'/' . $this->getName();

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
        $files = [
            'service_class' => $this->getPath(). '/' . $this->getName() . $this->service_suffix . '.php',
            'service_config' => $this->getPath() .'/Config/config.php'
        ];

        $dirs = [
            $this->getPath() .'/Core',
            $this->getPath() .'/Exceptions',
        ];

        foreach ($files as $stub => $file){
            if(!$this->filesystem->exists($file)){
                if (!$this->filesystem->isDirectory($dir = dirname($file))) {
                    $this->filesystem->makeDirectory($dir, 0775, true);
                }

                $this->filesystem->put($file, $this->getStubContents($stub));
            }
            $this->console->info("Created : {$file}");
        }

        foreach ($dirs as $dir){
            if (!$this->filesystem->isDirectory($dir)) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }
            $this->console->info("Created : {$dir}");
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasService($name)
    {
        $service = $this->getPath() . '/' . $name . $this->service_suffix . '.php';
        if (file_exists($service)) return true;
        return false;
    }

    /**
     * 生成Controller文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        if(!$this->hasBundle()){
            $this->console->error("The Bundle: [{$bundle_name}] none exist!");
            return;
        }

        $name = $this->getName();
        $service = $name.$this->service_suffix;
        if ($this->hasService($name)) {
            $this->console->error("The Service: [{$service}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Service <info>[{$service}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> ");
    }

    /**
     * 获取Service名称
     * @return string
     */
    protected function getServiceClassNameReplacement()
    {
        return $this->getName() . $this->service_suffix;
    }

    /**
     * 获取Service命名
     * @return string
     */
    protected function getServiceClassNamespaceReplacement()
    {
        $service= $this->rootConfig('generator.paths.service');

        return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $service) . '\\' . $this->getName();
    }

    /**
     * 获取包小写名
     * @return mixed
     */
    protected function getBundleLowerNameReplacement(){
        return $this->getLowerBundleName();
    }

    /**
     * 获取第三方名称
     * @return mixed
     */
    protected function getServiceVendorReplacement()
    {
        return $this->config('composer.service.vendor');
    }

    /**
     * 获取服务小写名
     * @return mixed
     */
    protected function getServiceLowerNameReplacement(){
        return $this->getLowerName();
    }

    /**
     * 获取作者名称
     * @return mixed
     */
    protected function getServiceAuthorNameReplacement()
    {
        return $this->author_name;
    }

    /**
     * 获取作者邮箱
     * @return mixed
     */
    protected function getServiceAuthorEmailReplacement()
    {
        return $this->author_email;
    }

    /**
     * 获取Bundle命名字符串
     * @return string
     */
    protected function getServiceNamespaceStrReplacement()
    {
        return str_replace('\\', '\\\\', $this->getServiceClassNamespaceReplacement());
    }


}