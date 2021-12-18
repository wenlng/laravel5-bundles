<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-28
 * Time: 13:42
 */
namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class EventGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    public function __construct($name)
    {
        if(stripos($name,'\\')){
            $this->name = basename($name);
            $this->path = dirname($name);
        }else{
            $this->name = $name;
        }

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
        if(null !== $this->path){
            return str_replace('\\', '/', $this->path);
        }else{
            $bundle_path = $this->getBundlePath();
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.event');
            return str_replace('\\', '/', $path);
        }
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
     * 生成Event文件
     */
    public function generateFiles()
    {
        if(null !== $this->path){
            $event_file = $this->getPath(). '/' . $this->getName(). '.php';
        }else{
            $event_file = $this->getPath(). '/' . $this->getName(). $this->event_suffix . '.php';
        }

        if(!$this->filesystem->exists($event_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($event_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }
            
            $this->filesystem->put($event_file, $this->getStubContents('event'));
        }
        $this->console->info("Created : {$event_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasEvent($name)
    {
        if(null !== $this->path){
            $event = $this->getPath() . '/' . $name .'.php';
        }else{
            $event = $this->getPath() . '/' . $name . $this->event_suffix .'.php';
        }

        if (file_exists($event)) return true;
        return false;
    }

    /**
     * 生成Event文件
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
        $event = $name. $this->event_suffix;
        if ($this->hasEvent($name)) {
            $this->console->error("The Event: [{$event}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Event <info>[{$event}]</info> created successfully in bundle: <info>[{$bundle_name}]</info>");
    }

    /**
     * 获取Event名称
     * @return string
     */
    protected function getEventNameReplacement()
    {
        if(null !== $this->path){
            return $this->getName();
        }else{
            return $this->getName() . $this->event_suffix;
        }
    }

    /**
     * 获取Event命名
     * @return string
     */
    protected function getEventNamespaceReplacement()
    {
        if(null !== $this->path){
            return str_replace('/', '\\', $this->path);
        }else{
            $event = $this->rootConfig('generator.paths.event', true);
            return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $event);
        }
    }

}