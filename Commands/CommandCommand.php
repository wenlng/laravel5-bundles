<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\CommandGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-command';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成command';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');

        foreach ($names as $name) {
            (new CommandGenerator($name))
                ->setConsole($this)
                ->setBundle($this->getTrimName($this->option('bundle')))
                ->setModule($this->getTrimName($this->option('module')))
                ->setCommandName($this->getTrimName($this->option('command')))
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
            ['command', 'c', InputOption::VALUE_OPTIONAL, '指定命令行命名'],
            ['bundle', 'b', InputOption::VALUE_REQUIRED, '指定bundle'],
            ['module', 'm', InputOption::VALUE_REQUIRED, '指定module'],
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
