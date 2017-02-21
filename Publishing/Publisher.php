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
     * @return string
     */
    abstract public function getTargetPath($bundle_name);


    /**
     * 获取资module源路径
     * @param $bundle_name
     * @return string
     */
    abstract public function getSourcePath($bundle_name);


    /**
     * 处理bundle下所有module的Translation
     * @param $bundle_name
     */
    public function publishBundle($bundle_name){
        if (!$this->getFilesystem()->isDirectory($source_path = $this->getSourcePath($bundle_name))) {
            $this->console->error("The bundle: [{$bundle_name}] is not setting [{$source_path}] path!");
            return;
        }

        if (!$this->getFilesystem()->isDirectory($destination_path = $this->getTargetPath($bundle_name))) {
            $this->getFilesystem()->makeDirectory($destination_path, 0775, true);
        }

        if ($this->getFilesystem()->copyDirectory($source_path, $destination_path)) {
            $this->console->info("Published: $source_path to {$destination_path}");

            $this->console->line("<info>Copied Directory</info>: bundle:<info>[{$bundle_name}]</info> resources.");
        } else {
            $this->console->error("<info>Copied Directory</info>: bundle:<info>[{$bundle_name}]</info> resources is fail! ");
        }
    }

}