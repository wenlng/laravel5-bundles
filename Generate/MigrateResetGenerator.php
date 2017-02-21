<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

class MigrateResetGenerator extends Generator
{
    /**
     * 获取目录
     * @param $bundle_name
     * @return mixed
     */
    public function getNamePath($bundle_name)
    {
        $bundle_path = $this->getBundleNamePath($bundle_name);
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.migration');
        return str_replace('\\', '/', $path);
    }

    /**
     * 处理bundle下所有module迁移
     * @param $bundle_name
     */
    public function rollbackBundle($bundle_name){
        $path = $this->getNamePath($bundle_name);

        $migrate = $this->createMigrate($path);
        $migrated = $migrate->reset();
        if (count($migrated)) {
            foreach ($migrated as $migration) {
                $this->console->line("<info>Migrate [{$migration}] to</info>: bundle:<info>[{$bundle_name}]</info>");
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

        $this->rollbackBundle($bundle_name);

        return;
    }

}