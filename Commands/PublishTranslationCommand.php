<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:28
 */

namespace Awen\Bundles\Commands;

use Awen\Bundles\publishing\TranslationPublisher;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class PublishTranslationCommand extends Command
{
    /**
     * 控制台命令的名称与签名
     * @var string
     */
    protected $name = 'bundle:publish-lang';

    /**
     * 控制台命令描述
     * @var string
     */
    protected $description = '刷新lang数据';

    /**
     * 执行控制台命令
     * @return mixed
     */
    public function fire()
    {
        $bundle = $this->getTrimName($this->option('bundle'));

        (new TranslationPublisher($bundle, $module))
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