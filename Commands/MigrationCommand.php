<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\MigrationGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-migration';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成Migration';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        (new MigrationGenerator($this->argument('name')))
        ->setConsole($this)
        ->setBundle($this->getTrimName($this->option('bundle')))
        ->setTable($this->getTrimName($this->option('table')))
        ->setFields($this->getTrimName($this->option('fields')))
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
            ['name', InputArgument::REQUIRED, 'Migration的名称'],
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
            ['fields', 'f', InputOption::VALUE_OPTIONAL, '指定字段'],
            ['table', 't', InputOption::VALUE_OPTIONAL, '指定table'],
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