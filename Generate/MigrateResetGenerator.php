<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class MigrateResetGenerator extends Generator
{
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
        return str_replace('\\', '/', $path);
    }

    /**
     * 处理bundle下所有module迁移
     * @param $bundle_name
     */
    public function rollbackBundle($bundle_name){
        $bundle = $this->app_kernel->getBundle($bundle_name);
        foreach ($bundle->getModules() as $name => $module){
            $this->rollbackModule($bundle_name, Str::studly($name));
        }
    }

    /**
     * 处理某个bundle下的module迁移
     * @param $bundle_name
     * @param $module_name
     */
    public function rollbackModule($bundle_name, $module_name){
        $path = $this->getNamePath($bundle_name, $module_name);

        $migrate = $this->createMigrate($path);
        $migrated = $migrate->reset();
        if (count($migrated)) {
            foreach ($migrated as $migration) {
                $this->console->line("<info>Migrate [{$migration}] to</info>: bundle:<info>[{$bundle_name}]</info> or module:<info>[{$module_name}]</info> ");
            }

            return;
        }

        $this->console->comment('Nothing to reset.');
    }

    /**
     * 生成迁移
     */
    public function reset()
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
            $this->rollbackModule($bundle_name, $module_name);
        }else{
            $this->rollbackBundle($bundle_name);
        }

        return;
    }

}