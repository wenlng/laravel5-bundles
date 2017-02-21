<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-28
 * Time: 13:42
 */
namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class ListenerGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var string
     */
    protected $event_path;

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
     * 设置模块
     * @param $event
     * @return $this
     */
    public function setEvent($event)
    {
        if(stripos($event,'\\')){
            $this->event = basename($event);
            $this->event_path = dirname($event);
        }else{
            $this->event = $event;
        }
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
     * 转大写规范
     * @return string
     */
    public function getEventName()
    {
        return Str::studly($this->event);
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
            $path = $bundle_path . '/' . $this->rootConfig('generator.paths.listener');
            return str_replace('\\', '/', $path);
        }
    }

    /**
     * 获取目录
     * @return mixed
     */
    public function getEventPath()
    {
        if(null !== $this->event_path){
            return str_replace('\\', '/', $this->event_path);
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
     * 生成Listener文件
     */
    public function generateFiles()
    {
        if(null !== $this->path){
            $listener_file = $this->getPath(). '/' . $this->getName(). '.php';
        }else{
            $listener_file = $this->getPath(). '/' . $this->getName(). $this->listener_suffix . '.php';
        }

        if(!$this->filesystem->exists($listener_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($listener_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($listener_file, $this->getStubContents('listener'));
        }
        $this->console->info("Created : {$listener_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasListener($name)
    {
        if(null !== $this->path){
            $listener = $this->getPath() . '/' . $name .'.php';
        }else{
            $listener = $this->getPath() . '/' . $name . $this->listener_suffix .'.php';
        }

        if (file_exists($listener)) return true;
        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasEvent($name)
    {
        if(empty($this->event)) return false;
        if(stripos($this->event,'\\')) return true;

        if(null !== $this->event_path){
            $event = $this->getEventPath() . '/' . $name .'.php';
        }else{
            $event = $this->getEventPath() . '/' . $name . $this->event_suffix .'.php';
        }

        if (file_exists($event)) return true;

        return false;
    }

    /**
     * 生成listener文件
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

        $event_name = $this->getEventName();
        $event = $event_name. $this->event_suffix;
        if(empty($event_name)){
            $this->console->error("Please appoint the event: -e EventName!");
            return;
        }
        if (!$this->hasEvent($event_name)) {
            $this->console->error("The Event: [{$event}] not exist!");
            return;
        }

        $name = $this->getName();
        $listener = $name. $this->listener_suffix;
        if ($this->hasListener($name)) {
            $this->console->error("The Listener: [{$listener}] already exist!");
            return;
        }

        $this->generateFiles();

        $this->console->line("Listener <info>[{$listener}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> ");
    }

    /**
     * 获取listener名称
     * @return string
     */
    protected function getListenerNameReplacement()
    {
        if(null !== $this->path){
            return $this->getName();
        }else{
            return $this->getName() . $this->listener_suffix;
        }
    }

    /**
     * 获取listener命名
     * @return string
     */
    protected function getListenerNamespaceReplacement()
    {
        if(null !== $this->path){
            return str_replace('/', '\\', $this->path);
        }else{
            $listener = $this->rootConfig('generator.paths.listener', true);
            return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $listener);
        }
    }

    /**
     * 获取Event名称
     * @return string
     */
    protected function getEventNameReplacement()
    {
        if(null !== $this->event_path){
            return $this->getEventName();
        }else{
            return $this->getEventName() . $this->event_suffix;
        }
    }

    /**
     * 获取Event命名
     * @return string
     */
    protected function getEventNamespaceReplacement()
    {
        if(null !== $this->event_path){
            return str_replace('/', '\\', $this->event_path);
        }else{
            $event = $this->rootConfig('generator.paths.event', true);
            return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $event);
        }
    }


}