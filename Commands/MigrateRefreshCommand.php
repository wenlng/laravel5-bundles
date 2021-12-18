<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-02-03
 * Time: 15:37
 */

namespace Awen\Bundles\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MigrateRefreshCommand extends Command
{

    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:migrate-refresh';

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
        $bundle = $this->getTrimName($this->option('bundle'));

        $params=[ '-b' => $bundle ];
        if($this->option('database')){
            $params['--database'] = $this->option('database');
        }
        if($this->option('force')){
            $params['--force'] = $this->option('force');
        }


        $this->call('bundle:migrate-reset', $params);
        $this->call('bundle:migrate', $params);

        if ($this->option('seed')) {
            $this->call('bundle:seed', [ '-b' => $bundle, ]);
        }
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
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
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