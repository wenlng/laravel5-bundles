<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Awen\Bundles\Component\Bundle;
use Awen\Bundles\Supports\NameParser;
use Illuminate\Support\Str;

class SeedGenerator extends Generator
{
    /**
     * @var string
     */
    protected $class;

    /**
     * 设置Class
     * @param $class
     * @return $this
     */
    public function setClass($class)
    {
        if (!empty($class)) $this->class = $class;
        return $this;
    }

    /**
     * 获取目录
     * @param $bundle_name
     * @param $module_name
     * @return mixed
     */
    public function getSeederName($bundle_name, $module_name)
    {
        $module_path = $this->getModuleNamePath($bundle_name, $module_name);
        $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.seeder');
        $_path = str_replace(base_path(), '', $path). '/'. Str::studly($module_name).'DatabaseSeeder';;
        return str_replace('/', '\\', trim($_path, '\\'));
    }

    /**
     * 处理bundle下所有module资源
     * @param $bundle_name
     */
    public function seedBundle($bundle_name){
        $bundle = $this->app_kernel->getBundle($bundle_name);

        if(is_null($bundle) || !($bundle instanceof Bundle)){
            $this->console->error("The bundle: [{$bundle_name}] not exist or not register!");
            return;
        }

        $modules = $this->getArrDefault($bundle->getModules(),[]);
        if(empty($modules)){
            $this->console->error("The bundle: [{$bundle_name}] not register modules!");
            return;
        }

        foreach ($modules as $name => $module){
            $this->seedModule($bundle_name, Str::studly($name));
        }
    }

    /**
     * 处理某个bundle下的module资源
     * @param $bundle_name
     * @param $module_name
     */
    public function seedModule($bundle_name, $module_name){
        $_class = $this->getSeederName($bundle_name, $module_name);
        $class = ucfirst($_class);

        if (class_exists($class)) {
            $this->dbSeed($bundle_name, $module_name);
            $this->console->line("<info>Seed to</info>: bundle:<info>[{$bundle_name}]</info> or module:<info>[{$module_name}]</info>.");
        } else {
            $this->console->error("bundle:[{$bundle_name}] or module:[{$module_name}] Class [$class] does not exists.");
        }
        return;
    }

    /**
     * 执行seed
     * @param $bundle_name
     * @param $module_name
     */
    protected function dbSeed($bundle_name, $module_name)
    {
        $params = [ '--class' => $this->class ?: $this->getSeederName($bundle_name, $module_name), ];

        if ($option = $this->console->option('database')) {
            $params['--database'] = $option;
        }

        $this->console->call('db:seed', $params);
    }

    /**
     * 生成Migrate
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        if(empty($bundle_name)){
            $this->console->error("Please appoint the bundle: -b BundleName!");
            return;
        }
        if (!$this->hasBundle()) {
            $this->console->error("The bundle: [{$bundle_name}] not exist!");
            return;
        }

        $module_name = $this->getModuleName();
        if(!empty($module_name)){
            if (!$this->hasModule()) {
                $this->console->error("The module: [{$module_name}] not exist!");
                return;
            }
        }

        if(!empty($bundle_name) && !empty($module_name)){
            $this->seedModule($bundle_name, $module_name);
        }else{
            $this->seedBundle($bundle_name);
        }

        return;
    }

}