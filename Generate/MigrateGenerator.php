<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

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
     * @return mixed
     */
    public function getNamePath($bundle_name)
    {
        $bundle_path = $this->getBundleNamePath($bundle_name);
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.migration');
        $_path = str_replace(base_path(), '', $path);
        return str_replace('\\', '/', $_path);
    }

    /**
     * 处理bundle下所有module迁移
     * @param $bundle_name
     */
    public function migrateBundle($bundle_name){
        $this->console->call('migrate', [
            '--path' => $this->getNamePath($bundle_name),
            '--database' => $this->console->option('database'),
            '--pretend' => $this->console->option('pretend'),
            '--force' => $this->console->option('force'),
        ]);

        $this->console->line("<info>Migrate to</info>: bundle:<info>[{$bundle_name}]</info>");

        if ($this->seed) {
            $this->console->call('bundle:seed', ['-b' => $bundle_name]);
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

        $this->migrateBundle($bundle_name);

        return;
    }

}