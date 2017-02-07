<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class CommandGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $command_execute_name = 'command:name';


    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    /**
     * 设置命令行名称
     * @param $name
     * @return $this
     */
    public function setCommandName($name)
    {
        if(!empty($name)) $this->command_execute_name = Str::snake($name, '_');
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
        $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.command');
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
     * 生成Command文件
     */
    public function generateFiles()
    {
        $command_file = $this->getPath(). '/' . $this->getName() . $this->command_suffix . '.php';
        if(!$this->filesystem->exists($command_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($command_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($command_file, $this->getStubContents('command'));
        }
        $this->console->info("Created : {$command_file}");
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCommand($name)
    {
        $command = $this->getPath() . '/' . $name . $this->command_suffix . '.php';
        if (file_exists($command)) return true;
        return false;
    }


    /**
     * 生成Command文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        $module_name = $this->getModuleName();
        if(!$this->hasBundleOrModule($bundle_name, $module_name)) return;

        $name = $this->getName();
        $command = $name.$this->command_suffix;
        if ($this->hasCommand($name)) {
            $this->console->error("The Command: [{$command}] already exist!");
            return;
        }

        $this->generateFiles();

        $command_name = $this->config('console_suffix') . '-' . $this->command_execute_name;
        $this->console->info("---------------------------------------------------------------------");
        $this->console->info("| Please register command in bundle: [{$bundle_name}] to module: [{$module_name}]|");
        $this->console->info("| Then execute command: [{$command_name}] |");
        $this->console->info("---------------------------------------------------------------------");

        $this->console->line("Command <info>[{$command}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> to module: <info>[{$module_name}]</info> ");
    }


    /**
     * 获取Command名称
     * @return string
     */
    protected function getCommandNameReplacement()
    {
        return $this->getName() . $this->command_suffix;
    }

    /**
     * 获取Command命名
     * @return string
     */
    protected function getCommandNamespaceReplacement()
    {
        $command = $this->rootConfig('modules.generator.paths.command');
        return $this->getModuleCurrentNamespace() . '\\' . str_replace('/', '\\', $command);
    }

    /**
     * 获取Command执行命令名称
     * @return string
     */
    protected function getCommandExecuteNameReplacement()
    {
        return $this->command_execute_name;
    }
}