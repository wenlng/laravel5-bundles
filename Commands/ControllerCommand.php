<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\ControllerGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:make-controller';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '生成controller';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $names = $this->argument('name');

        foreach ($names as $name) {
            (new ControllerGenerator($name))
                ->setConsole($this)
                ->setBundle($this->getTrimName($this->option('bundle')))
                ->setCate($this->getTrimName($this->option('cate')))
                ->setPathSuffix($this->getTrimName($this->option('path')))
                ->setExtend($this->getTrimName($this->option('extend')))
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
            ['name', InputArgument::IS_ARRAY, 'Controller的名称'],
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
            ['cate', 'c', InputOption::VALUE_OPTIONAL, '指定-c=a(api)还是-c=v(view)'],
            ['path', 'p', InputOption::VALUE_OPTIONAL, '指定-p= index 表示在Controller目录下的index目录下生成' ],
            ['extend', 'e', InputOption::VALUE_OPTIONAL, '指定继承Controller'],
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