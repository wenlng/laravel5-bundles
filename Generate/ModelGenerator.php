<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Illuminate\Support\Str;

class ModelGenerator extends Generator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $cate = 'm';

    /**
     * @var string
     */
    protected $all = false;

    /**
     * @var string
     */
    protected $id;

    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    /**
     * 设置实体与仓库
     * @param $cate
     * @return $this
     */
    public function setCate($cate)
    {
        if (!empty($cate) && strtolower($cate) == 'r') $this->cate = strtolower($cate);
        return $this;
    }

    /**
     * 设置实体ID
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        if (!empty($id)) $this->id = Str::snake($id, '_');
        return $this;
    }

    /**
     * 设置是否全部都生成
     * @param $all
     * @return $this
     */
    public function setAll($all)
    {
        $this->all = $all;
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
     * 获取Model目录
     * @return mixed
     */
    public function getModelPath()
    {
        $bundle_path = $this->getBundlePath();
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.model');
        return str_replace('\\', '/', $path);
    }

    /**
     * 获取Repository目录
     * @return mixed
     */
    public function getRepositoryPath()
    {
        $bundle_path = $this->getBundlePath();
        $path = $bundle_path . '/' . $this->rootConfig('generator.paths.repository');
        return str_replace('\\', '/', $path);
    }

    /**
     * 获取模板内容，并替换内容中的变量
     * @param $stub
     * @return string
     */
    protected function getStubContents($stub)
    {
        return $this->createStub('/' . $stub . '.stub', $this->getReplacement($stub))->render();
    }

    /**查找将要替换的值
     * @param $stub
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->rootConfig('replacements');
        return $this->_getReplacement($stub, $replacements);
    }

    /**
     * 替换文本中的变量
     * @param $stub
     * @param $str
     * @return mixed
     */
    protected function getStubStr($stub, $str)
    {
        foreach ($this->getReplacement($stub) as $search => $replace) {
            $str = str_replace('%' . strtoupper($search) . '%', $replace, $str);
        }
        return $str;
    }

    /**
     * 生成Model
     */
    public function generateModelFile()
    {
        $model_file = $this->getModelPath() . '/' . $this->getName() . $this->model_suffix . '.php';
        if (!$this->filesystem->exists($model_file)) {
            if (!$this->filesystem->isDirectory($dir = dirname($model_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($model_file, $this->getStubContents('model'));
        }
        $this->console->info("Created : {$model_file}");
    }

    /**
     * 生成Repository
     */
    public function generateRepositoryFile()
    {
        $repository_file = $this->getRepositoryPath() . '/' . $this->getName() . $this->repository_suffix . '.php';
        if (!$this->filesystem->exists($repository_file)) {
            if (!$this->filesystem->isDirectory($dir = dirname($repository_file))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($repository_file, $this->getStubContents('repository'));
        }
        $this->console->info("Created : {$repository_file}");
    }

    /**
     * 生成Model 或者 Repository文件
     */
    public function generateFiles()
    {
        if ($this->cate == 'm') {
            $this->generateModelFile();
        } else {
            $this->generateRepositoryFile();
        }

        if ($this->all && $this->cate == 'm') {
            $this->generateRepositoryFile();
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasModel($name)
    {
        $model = $this->getModelPath() . '/' . $name . $this->model_suffix . '.php';
        if (file_exists($model)) return true;
        return false;
    }


    /**
     * @param $name
     * @return bool
     */
    public function hasRepository($name)
    {
        $repository = $this->getRepositoryPath() . '/' . $name . $this->repository_suffix . '.php';
        if (file_exists($repository)) return true;
        return false;
    }

    /**
     * 生成Model | Repository文件
     */
    public function generate()
    {
        $bundle_name = $this->getBundleName();
        if(empty($bundle_name)){
            $this->console->error("Please appoint the bundle: -b BundleName!");
            return ;
        }
        if (!$this->hasBundle()) {
            $this->console->error("The bundle: [{$bundle_name}] not exist!");
            return ;
        }

        $name = $this->getName();
        $repository = $name . $this->repository_suffix;
        if ($this->cate == 'm') {
            //生成model
            if ($this->hasModel($name)) {
                $this->console->error("The Model: [{$name}] already exist!");
                return;
            }
        } else {
            //生成repository
            if ($this->hasRepository($name)) {
                $this->console->error("The Repository: [{$repository}] already exist!");
                return;
            }
        }

        if ($this->all && $this->cate == 'm') {
            //先前就检查了model部分，如果两者都生成，这时再检查repository部分
            if ($this->hasRepository($name)) {
                $this->console->error("The Repository: [{$repository}] already exist!");
                return;
            }
        }

        $this->generateFiles();

        if ($this->cate == 'm') {
            $this->console->line("Model <info>[{$name}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> ");
        } else {
            $this->console->line("Repository <info>[{$repository}]</info> created successfully in bundle: <info>[{$bundle_name}]</info>");
        }

        if ($this->all && $this->cate == 'm') {
            $this->console->line("Repository <info>[{$repository}]</info> created successfully in bundle: <info>[{$bundle_name}]</info> ");
        }
    }

    /**
     * 获取Model名称
     * @return string
     */
    protected function getModelLowerNameReplacement()
    {
        return $this->getLowerName();
    }

    /**
     * 获取Model名称
     * @return string
     */
    protected function getModelLowerIdReplacement()
    {
        return empty($this->id) ? $this->getLowerName() . '_id' : $this->id;
    }

    /**
     * 获取Model名称
     * @return string
     */
    protected function getModelNameReplacement()
    {
        return $this->getName() . $this->model_suffix;
    }

    /**
     * 获取Repository名称
     * @return string
     */
    protected function getRepositoryNameReplacement()
    {
        return $this->getName() . $this->repository_suffix;
    }

    /**
     * 获取Model命名
     * @return string
     */
    protected function getModelNamespaceReplacement()
    {
        $model = $this->rootConfig('generator.paths.model', true);
        return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $model);
    }

    /**
     * 获取Repository命名
     * @return string
     */
    protected function getRepositoryNamespaceReplacement()
    {
        $repository = $this->rootConfig('generator.paths.repository', true);
        return $this->getBundleCurrentNamespace() . '\\' . str_replace('/', '\\', $repository);
    }
}