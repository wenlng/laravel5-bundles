<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Component;

use Illuminate\Config\Repository;
use Illuminate\Console\Command as BasCommand;
use Illuminate\Foundation\Application;

class Command extends BasCommand
{
    /**
     * @var static
     */
    private $app;

    /**
     * @var Repository
     */
    protected $config;

    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->config = $this->app['config'];

        $suffix = $this->config->get('bundles.console_suffix');
        $this->name = $suffix . '-'. $this->name;
        parent::__construct();
    }

}