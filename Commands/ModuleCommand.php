<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\ModuleGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-module';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成Module';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');

        foreach ($names as $name) {
            (new ModuleGenerator($name))
                ->setConsole($this)
                ->setBundle($this->getBundleName())
                ->setForce($this->option('force'))
                ->setClean($this->option('clean'))
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
            ['name', InputArgument::IS_ARRAY, 'Module的名称'],
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
            ['clean', 'c', InputOption::VALUE_NONE, '生成一个模块不要小实例'],
            ['force', 'f', InputOption::VALUE_NONE, '如果模块存在时强制删除旧的生成'],
        ];
    }

    /**
     * @return string
     */
    private function getBundleName()
    {
        $bundle = $this->option('bundle');
        return ltrim($bundle, '=,:');
    }
}