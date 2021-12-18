<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
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
     * @return mixed
     */
    public function getSeederName($bundle_name)
    {
        $bundle_path = $this->getBundleNamePath($bundle_name);
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.seeder');
        $_path = str_replace(base_path(), '', $path). '/'. $this->getBundleName().'DatabaseSeeder';;
        return str_replace('/', '\\', trim($_path, '\\'));
    }

    /**
     * 处理bundle下所有module资源
     * @param $bundle_name
     */
    public function seedBundle($bundle_name){
        $_class = $this->getSeederName($bundle_name);
        $class = ucfirst($_class);

        if (class_exists($class)) {
            $this->dbSeed($bundle_name);
            $this->console->line("<info>Seed to</info>: bundle:<info>[{$bundle_name}]</info>.");
        } else {
            $this->console->error("bundle:[{$bundle_name}] Class [$class] does not exists.");
        }
        return;

    }

    /**
     * 执行seed
     * @param $bundle_name
     */
    protected function dbSeed($bundle_name)
    {
        $params = [ '--class' => $this->class ?: $this->getSeederName($bundle_name), ];

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

        $this->seedBundle($bundle_name);

        return;
    }

}