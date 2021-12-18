<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\MigrateResetGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MigrateResetCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:migrate-reset';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '操作Migrate';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        (new MigrateResetGenerator())
        ->setConsole($this)
        ->setBundle($this->getTrimName($this->option('bundle')))
        ->reset();
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