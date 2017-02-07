<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-28
 * Time: 13:42
 */
namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class JobGenerator extends Generator
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
        $module_path = $this->getModulePath();
        $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.job');
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
     * 生成job文件
     */
    public function generateFiles()
    {
        $job_file = $this->getPath(). '/' . $this->getName(). $this->job_suffix . '.php';
        if(!$this->filesystem->exists($job_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($job_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($job_file, $this->getStubContents('create_job'));
        }
        $this->console->info("Created : {$job_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasJob($name)
    {
        $job = $this->getPath() . '/' . $name . $this->job_suffix .'.php';
        if (file_exists($job)) return true;
        return false;
    }

    /**
     * 生成Job文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        $module_name = $this->getModuleName();
        if(!$this->hasBundleOrModule($bundle_name, $module_name)) return;

        $name = $this->getName();
        $job = $name. $this->job_suffix;
        if ($this->hasJob($name)) {
            $this->console->error("The Job: [{$job}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Job <info>[{$job}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> to module: <info>[{$module_name}]</info> ");
    }

    /**
     * 获取job名称
     * @return string
     */
    protected function getJobNameReplacement()
    {
        return $this->getName() . $this->job_suffix;
    }

    /**
     * 获取job命名
     * @return string
     */
    protected function getJobNamespaceReplacement()
    {
        $job = $this->rootConfig('modules.generator.paths.job');
        return $this->getModuleCurrentNamespace() . '\\' . str_replace('/', '\\', $job);
    }

}