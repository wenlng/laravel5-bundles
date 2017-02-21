<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\BundleGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BundleCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成Bundle';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');

        foreach ($names as $name) {
            (new BundleGenerator($name))
                ->setConsole($this)
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
            ['name', InputArgument::IS_ARRAY, 'Bundle的名称'],
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
            ['clean', 'c', InputOption::VALUE_NONE, '生成一个模块不要小实例'],
            ['force', 'f', InputOption::VALUE_NONE, '如果Bundle存在时强制删除旧的重新生成.'],
        ];
    }
}