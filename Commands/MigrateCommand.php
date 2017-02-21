<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\Generate\MigrateGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MigrateCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:migrate';

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
        (new MigrateGenerator())
        ->setConsole($this)
        ->setBundle($this->getTrimName($this->option('bundle')))
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
            ['pretend', 'p', InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['seed', 's', InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
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