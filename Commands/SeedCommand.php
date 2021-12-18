<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\SeedGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class SeedCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:seed';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '操作Seed';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        (new SeedGenerator())
        ->setConsole($this)
        ->setBundle($this->getTrimName($this->option('bundle')))
        ->setClass($this->option('class'))
        ->generate();
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
            ['database', 'db', InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['class', 'c', InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', null]
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