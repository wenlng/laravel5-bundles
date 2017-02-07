<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\ModelGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-model';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成model';

    /**
     * 执行控制台命令
     *
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');

        foreach ($names as $name) {
            (new ModelGenerator($name))
                ->setConsole($this)
                ->setBundle($this->getTrimName($this->option('bundle')))
                ->setModule($this->getTrimName($this->option('module')))
                ->setCate($this->getTrimName($this->option('cate')))
                ->setId($this->getTrimName($this->option('id')))
                ->setAll($this->getTrimName($this->option('all')))
                ->generate();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::IS_ARRAY, 'Command的名称'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['bundle', 'b', InputOption::VALUE_REQUIRED, '指定bundle'],
            ['module', 'm', InputOption::VALUE_REQUIRED, '指定module'],
            ['cate', 'c', InputOption::VALUE_OPTIONAL, '指定生成实体还是仓库，默认实体'],
            ['id', 'i', InputOption::VALUE_OPTIONAL, '实体ID'],
            ['all', 'a', InputOption::VALUE_NONE, '生成实体并生成仓库'],
        ];
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getTrimName($name)
    {
        return ltrim($name, '=,:');
    }


}