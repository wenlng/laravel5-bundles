<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-02-01
 * Time: 17:55
 */
namespace Awen\Bundles\publishing;

use Awen\Bundles\Component\Bundle;
use Awen\Bundles\Repositories\ResourcesRepository;
use Illuminate\Support\Str;

abstract class Publisher extends ResourcesRepository
{
    /**
     * 获取资源PATH
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    abstract public function getTargetPath($bundle_name, $module_name);


    /**
     * 获取资module源路径
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    abstract public function getSourcePath($bundle_name, $module_name);


    /**
     * 处理bundle下所有module的Translation
     * @param $bundle_name
     */
    public function publishBundle($bundle_name){
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
            $this->publishModule($bundle_name, Str::studly($name));
        }
    }

    /**
     * 处理某个bundle下的module的Translation
     * @param $bundle_name
     * @param $module_name
     */
    public function publishModule($bundle_name, $module_name){
        if (!$this->getFilesystem()->isDirectory($source_path = $this->getSourcePath($bundle_name, $module_name))) {
            $this->console->error("The bundle: [{$bundle_name}] is not setting [{$source_path}] path!");
            return;
        }

        if (!$this->getFilesystem()->isDirectory($destination_path = $this->getTargetPath($bundle_name, $module_name))) {
            $this->getFilesystem()->makeDirectory($destination_path, 0775, true);
        }

        if ($this->getFilesystem()->copyDirectory($source_path, $destination_path)) {
            $this->console->info("Published: $source_path to {$destination_path}");

            $this->console->line("<info>Copied Directory</info>: bundle:<info>[{$bundle_name}]</info> or module:<info>[{$module_name}]</info> resources.");
        } else {
            $this->console->error("<info>Copied Directory</info>: bundle:<info>[{$bundle_name}]</info> or module:<info>[{$module_name}]</info> resources is fail! ");
        }
    }

}