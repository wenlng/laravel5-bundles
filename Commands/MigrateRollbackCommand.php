<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\MigrateRollbackGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MigrateRollbackCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:migrate-rollback';

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
        (new MigrateRollbackGenerator())
        ->setConsole($this)
        ->setBundle($this->getTrimName($this->option('bundle')))
        ->rollback();
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