<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\EventGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EventCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-event';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成Event';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');

        foreach ($names as $name) {
            (new EventGenerator($name))
                ->setConsole($this)
                ->setBundle($this->getTrimName($this->option('bundle')))
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
            ['name', InputArgument::IS_ARRAY, 'Event的名称'],
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