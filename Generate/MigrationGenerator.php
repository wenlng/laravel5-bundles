<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class MigrationGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $fields;

    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    /**
     * 设置Table
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        if (!empty($table)) $this->table = $table;
        return $this;
    }

    /**
     * 设置sFields
     * @param $fields
     * @return $this
     */
    public function setFields($fields)
    {
        if (!empty($fields)) $this->fields = $fields;
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
     * @return string
     */
    private function getFileName()
    {
        return date('Y_m_d_His_').$this->getLowerName();
    }

    /**
     * 获取目录
     * @return mixed
     */
    public function getPath()
    {
        $module_path = $this->getModulePath();
        $path = $module_path . '/' . $this->rootConfig('modules.generator.paths.migration');
        return str_replace('\\', '/', $path);
    }

    /**
     * 获取模板内容
     * @return \Awen\Bundles\Supports\Stub
     */
    protected function getTemplateContents()
    {
        $parser = $this->createNameParser($this->getLowerName());
        $schema_parser = $this->createSchemaParser($this->fields);

        if ($parser->isCreate()) {
            $replacement = [
                'NAME' => $this->getName(),
                'TABLE' => $parser->getTable(),
                'FIELDS' => $schema_parser->render(),
            ];
            return $this->createStub('/migration/create.stub', $replacement)->render();
        } elseif ($parser->isAdd()) {
            $replacement = [
                'NAME' => $this->getName(),
                'TABLE' => $parser->getTable(),
                'FIELDS_UP' => $schema_parser->up(),
                'FIELDS_DOWN' => $schema_parser->down(),
            ];
            return $this->createStub('/migration/add.stub',$replacement);
        } elseif ($parser->isDelete()) {
            $replacement = [
                'NAME' => $this->getName(),
                'TABLE' => $parser->getTable(),
                'FIELDS_DOWN' => $schema_parser->up(),
                'FIELDS_UP' => $schema_parser->down(),
            ];
            return $this->createStub('/migration/delete.stub',$replacement);
        } elseif ($parser->isDrop()) {
            $replacement = [
                'NAME' => $this->getName(),
                'TABLE' => $parser->getTable(),
                'FIELDS' => $schema_parser->render(),
            ];
            return $this->createStub('/migration/drop.stub',$replacement);
        }

        throw new \InvalidArgumentException('Invalid migration name');
    }

    /**
     * 生成Migration文件
     */
    public function generateFiles()
    {
        $migration_file = $this->getPath(). '/' . $this->getFileName() . '.php';
        if(!$this->filesystem->exists($migration_file)){
            if (!$this->filesystem->isDirectory($dir = dirname($migration_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($migration_file, $this->getTemplateContents());
        }
        $this->console->info("Created : {$migration_file}");
    }

    /**
     * 生成Migration文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        $module_name = $this->getModuleName();
        if(!$this->hasBundleOrModule($bundle_name, $module_name)) return;

        $name = $this->getLowerName();
        $this->generateFiles();

        $this->console->line("Migration <info>[{$name}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> to module: <info>[{$module_name}]</info> ");
    }

}