<?php
/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 12:47
 */

namespace Awen\Bundles\Providers;

use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /** 命令名命名空间
     * @var string
     */
    protected $namespace = 'Awen\\Bundles\\Commands\\';

    /**
     * 给定的命令名
     *
     * @var array
     */
    protected $commands = [
        'Bundle',
        'Controller',
        'Command',
        'Model',
        'Middleware',
        'Provider',
        'Request',
        'Job',
        'Event',
        'Listener',
        'GenerateEvent',
        'Publish',
        'PublishTranslation',
        'PublishMigration',
        'Migration',
        'Migrate',
        'SeedMake',
        'Seed',
        'MigrateRollback',
        'MigrateReset',
        'MigrateRefresh',
        'Service',
    ];


    /**
     * 注册命令行
     */
    public function register()
    {
        foreach ($this->commands as $command) {
            $this->commands($this->namespace . $command . 'Command');
        }
    }

    /**
     * @return array
     */
    public function provides()
    {
        $provides = [];

        foreach ($this->commands as $command) {
            $provides[] = $this->namespace . $command . 'Command';
        }

        return $provides;
    }
}