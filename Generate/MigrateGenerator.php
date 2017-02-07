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

class MigrateGenerator extends Generator
{
    protected $seed;

    /**
     * 设置Seed
     * @param $seed
     * @return $this
     */
    protected function setSeed($seed){
        $this->seed = $seed;
        return $this;
    }

    /**
     * 获取目录
     * @param $bundle_name
     * @param $module_name
     * @return mixed
     */
    public function getNamePath($bundle_name, $module_name)
    {
        $module_path = $this->getModuleNamePath($bundle_name, $module_name);
        $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.migration');
        $_path = str_replace(base_path(), '', $path);
        return str_replace('\\', '/', $_path);
    }

    /**
     * 处理bundle下所有module迁移
     * @param $bundle_name
     */
    public function migrateBundle($bundle_name){
        $bundle = $this->app_kernel->getBundle($bundle_name);
        if(empty($bundle) || !($bundle instanceof Bundle)){
            $this->console->error("The bundle: [{$bundle_name}] not exist or not register!");
            return;
        }

        $modules = $this->getArrDefault($bundle->getModules(),[]);
        if(empty($modules)){
            $this->console->error("The bundle: [{$bundle_name}] not register modules!");
            return;
        }

        foreach ($modules as $name => $module){
            $this->migrateModule($bundle_name, Str::studly($name));
        }
    }

    /**
     * 处理某个bundle下的module迁移
     * @param $bundle_name
     * @param $module_name
     */
    public function migrateModule($bundle_name, $module_name){
        $this->console->call('migrate', [
            '--path' => $this->getNamePath($bundle_name, $module_name),
            '--database' => $this->console->option('database'),
            '--pretend' => $this->console->option('pretend'),
            '--force' => $this->console->option('force'),
        ]);

        $this->console->line("<info>Migrate to</info>: bundle:<info>[{$bundle_name}]</info> or module:<info>[{$module_name}]</info> ");

        if ($this->seed) {
            $this->console->call('bundle:seed', ['-b' => $bundle_name, '-m' => $module_name]);
        }
    }

    /**
     * 生成迁移
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
            $this->migrateModule($bundle_name, $module_name);
        }else{
            $this->migrateBundle($bundle_name);
        }

        return;
    }

}