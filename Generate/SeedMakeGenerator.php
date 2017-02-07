<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class SeedMakeGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $database;

    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    /**
     * 设置database
     * @param $database
     * @return $this
     */
    public function setDatabase($database)
    {
        if (!empty($database)) $this->database = $database;
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
     * Get seeder name.
     *
     * @return string
     */
    private function getSeederName()
    {
        $end = $this->database ? 'DatabaseSeeder' : 'TableSeeder';

        return $this->getName().$end;
    }

    /**
     * 获取目录
     * @return mixed
     */
    public function getPath()
    {
        $module_path = $this->getModulePath();
        $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.seeder');
        return str_replace('\\', '/', $path);
    }

    /**
     * 获取Middleware命名
     * @return string
     */
    protected function getSeedNamespace()
    {
        $seeder = $this->rootConfig('modules.generator.paths.seeder');
        return $this->getModuleCurrentNamespace() . '\\' . str_replace('/', '\\', $seeder);
    }

    /**
     * 获取模板内容
     * @return \Awen\Bundles\Supports\Stub
     */
    protected function getTemplateContents()
    {
        $replacement = [
            'SEED_NAME' => $this->getSeederName(),
            'SEED_NAMESPACE' => $this->getSeedNamespace(),
        ];
        
        return $this->createStub('/seeder.stub', $replacement)->render();
    }

    /**
     * 生成Migration文件
     */
    public function generateFiles()
    {
        $migration_file = $this->getPath(). '/' . $this->getSeederName() . '.php';

        if(!$this->filesystem->exists($migration_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($migration_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($migration_file, $this->getTemplateContents());
        }
        $this->console->info("Created : {$migration_file}");
    }

    /**
     * 生成Seed文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        $module_name = $this->getModuleName();
        if(!$this->hasBundleOrModule($bundle_name, $module_name)) return;

        $name = $this->getLowerName();
        $this->generateFiles();

        $this->console->line("Seed <info>[{$name}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> to module: <info>[{$module_name}]</info> ");
    }

}