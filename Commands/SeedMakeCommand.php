<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\SeedMakeGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SeedMakeCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-seed';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成Seed';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        (new SeedMakeGenerator($this->argument('name')))
        ->setConsole($this)
        ->setBundle($this->getTrimName($this->option('bundle')))
        ->setModule($this->getTrimName($this->option('module')))
        ->setDatabase($this->option('database'))
        ->generate();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'seed的名称'],
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
            ['database', 'd', InputOption::VALUE_NONE, '是表还是数据库'],
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