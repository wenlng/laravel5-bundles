<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\publishing\AssetPublisher;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class PublishCommand extends Command
{
    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:publish';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '刷新asset数据';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $bundle = $this->getTrimName($this->option('bundle'));

        (new AssetPublisher($bundle))
            ->setConsole($this)
            ->publish();
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