<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

class BundleGenerator extends Generator
{
    public function __construct($name)
    {
        parent::__construct();

        $this->setBundle($name);
    }

    /**
     * 获取bundle需要生成的目录
     * @return array
      */
    public function getBundleFolders()
    {
        return array_values($this->rootConfig('bundles.generator.paths'));
    }


    /**
     * 获取包的所有需要生成的文件
     * @return mixed
     */
    public function getBundleFiles()
    {
        return  $this->rootConfig('bundles.generator.files');
    }

    /**
     * 生成目录
     */
    public function generateFolders()
    {
        foreach ($this->getBundleFolders() as $folder) {
            $path = $this->getBundlePath() . '/' . $folder;
            if(!is_dir($path)) $this->filesystem->makeDirectory($path, 0755, true);
            $this->generateGitKeep($path);
        }
    }

    /**
     * 获取模板内容，并替换内容中的变量
     * @param $stub
     * @return string
     */
    protected function getStubContents($stub)
    {
        return $this->createStub('/'.$stub.'.stub', $this->getReplacement($stub))->render();
    }

    /**查找将要替换的值
     * @param $stub
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->rootConfig('bundles.replacements');
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
            $str = str_replace('%'.strtoupper($search).'%', $replace, $str);
        }
        return $str;
    }

    /**
     * 生成Bundle文件
     */
    public function generateFiles(){
        $kernel_file = $this->getBundleRootPath(). '/' . $this->rootConfig('kernel') .'.php';
        if(!$this->filesystem->exists($kernel_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($kernel_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($kernel_file, $this->getStubContents('kernel'));
        }

        foreach ($this->getBundleFiles() as $stub => $file) {
            $path = $this->getBundlePath().'/'.$this->getStubStr($stub, $file);
            if (!$this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));
            $this->console->info("Created : {$path}");
        }
    }

    /**
     * 删除旧的
     * @return bool
     */
    public function deleteOld(){
        $bundle = $this->getBundlePath();
        return $this->filesystem->deleteDirectory($bundle, true);
    }

    /**
     * 提示输入作者信息
     */
    public function inputAuthorInfo(){
        $default_name = $this->config('composer.bundle.author.name') ?: 'name';
        $default_email = $this->config('composer.bundle.author.email') ?: 'email';

        $this->author_name = $this->console->ask("Please input author name: ", $default_name);
        $this->author_email = $this->console->ask("Please input author email: ", $default_email);
    }

    /**
     * 生成Bundle
     */
    public function generate()
    {
        $name = $this->getBundleName();
        if ($this->hasBundle()){
            if ($this->force) {
                $this->console->warn("The bundle: [{$name}] already exist!");
                $confirm = $this->console->confirm('Are you sure, the old bundle will clear!', false);
                if(!$confirm) return;

                $bundle = $this->app_kernel->getBundle($name);
                if($bundle)
                    $bundle->delete();
                else
                    $this->deleteOld();
            } else {
                $this->console->error("The bundle: [{$name}] already exist!");
                return;
            }
        }

        $this->inputAuthorInfo();
        $this->generateFolders();
        $this->generateFiles();

        $this->console->line("Bundle <info>[{$name}]</info> created successfully.");
    }


    /**
     * 获取转小写的名称
     * @return mixed
     */
    protected function getBundleLowerNameReplacement()
    {
        return $this->getLowerBundleName();
    }

    /**
     * 获取内核名称
     * @return mixed
     */
    protected function getKernelNameReplacement()
    {
        return $this->rootConfig('kernel');
    }

    /**
     * 获取Bundle名称
     * @return string
     */
    protected function getBundleNameReplacement()
    {
        return $this->getBundleName() . $this->bundle_suffix;
    }

    /**
     * 获取骆峰式名称
     * @return string
     */
    protected function getBundleStudlyNameReplacement()
    {
        return $this->getBundleName();
    }

    /**
     * 获取服务名称
     * @return mixed
     */
    protected function getServiceNameReplacement(){
        return $this->rootConfig('service.name');
    }

    /**
     * 获取服务命名
     * @return mixed
     */
    protected function getServiceNamespaceReplacement(){
        return $this->rootConfig('service.namespace');
    }

    /**
     * 获取第三方名称
     * @return mixed
     */
    protected function getBundleVendorReplacement()
    {
        return $this->config('composer.bundle.vendor');
    }

    /**
     * 获取作者名称
     * @return mixed
     */
    protected function getBundleAuthorNameReplacement()
    {
        return $this->author_name;
    }

    /**
     * 获取作者邮箱
     * @return mixed
     */
    protected function getBundleAuthorEmailReplacement()
    {
        return $this->author_email;
    }

    /**
     * 获取Bundle命名
     * @return string
     */
    protected function getBundleNamespaceReplacement()
    {
        $bas_namespace = str_replace('\\', '\\\\', $this->rootConfig('namespace'));
        return $bas_namespace . '\\' . $this->getBundleName();
    }

    /**
     * 获取Bundle命名字符串
     * @return string
     */
    protected function getBundleNamespaceStrReplacement()
    {
        $bas_namespace = str_replace('\\', '\\\\', $this->rootConfig('namespace'));
        return $bas_namespace . '\\\\' . $this->getBundleName();
    }

}