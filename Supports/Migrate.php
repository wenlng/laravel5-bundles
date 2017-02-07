<?php

namespace Awen\Bundles\Supports;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

class Migrate
{
    /**
     * Laravel Application instance.
     * @var Application.
     */
    protected $app;

    /**
     * The laravel filesystem instance.
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The laravel db instance.
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @var string
     */
    protected $path;

    public function __construct(Application $app, $path)
    {
        $this->path = $path;
        $this->app = $app;
        $this->filesystem = $this->app['files'];
        $this->db = $this->app['db'];
    }

    /**
     * Get migration path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get migration files.
     *
     * @param boolean $reverse
     * @return array
     */
    public function getMigrations($reverse = false)
    {
        $files = $this->filesystem->glob($this->getPath().'/*_*.php');
        if ($files === false) {
            return array();
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));

        }, $files);
        sort($files);

        if ($reverse) {
            return array_reverse($files);
        }

        return $files;
    }

    /**
     * Rollback migration.
     *
     * @return array
     */
    public function rollback()
    {
        $migrations = $this->getLast($this->getMigrations(true));

        $this->requireFiles($migrations->toArray());

        $migrated = [];

        foreach ($migrations as $migration) {
            $data = $this->find($migration);

            if ($data->count()) {
                $migrated[] = $migration;

                $this->down($migration);

                $data->delete();
            }
        }

        return $migrated;
    }

    /**
     * Reset migration.
     *
     * @return array
     */
    public function reset()
    {
        $migrations = $this->getMigrations(true);

        $this->requireFiles($migrations);

        $migrated = [];

        foreach ($migrations as $migration) {
            $data = $this->find($migration);

            if ($data->count()) {
                $migrated[] = $migration;

                $this->down($migration);
              
                $data->delete();
            }
        }

        return $migrated;
    }

    /**
     * Run down schema from the given migration name.
     *
     * @param string $migration
     */
    public function down($migration)
    {
        $this->resolve($migration)->down();
    }

    /**
     * Run up schema from the given migration name.
     *
     * @param string $migration
     */
    public function up($migration)
    {
        $this->resolve($migration)->up();
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param string $file
     *
     * @return object
     */
    public function resolve($file)
    {
        $file = implode('_', array_slice(explode('_', $file), 4));

        $class = studly_case($file);

        return new $class();
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param array  $files
     */
    public function requireFiles(array $files)
    {
        $path = $this->getPath();

        foreach ($files as $file) {
            $this->filesystem->requireOnce($path.'/'.$file.'.php');
        }
    }

    /**
     * Get table instance.
     *
     * @return mixed
     */
    public function table()
    {
        return $this->db->table(config('database.migrations'));
    }

    /**
     * Find migration data from database by given migration name.
     *
     * @param string $migration
     *
     * @return object
     */
    public function find($migration)
    {
        return $this->table()->whereMigration($migration);
    }

    /**
     * Save new migration to database.
     *
     * @param string $migration
     *
     * @return mixed
     */
    public function log($migration)
    {
        return $this->table()->insert([
            'migration' => $migration,
            'batch' => $this->getNextBatchNumber(),
        ]);
    }

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the last migration batch number.
     *
     * @param array $migrations
     * @return int
     */
    public function getLastBatchNumber($migrations = null)
    {
        return $this->table()
            ->whereIn('migration', $migrations)
            ->max('batch');
    }

    /**
     * Get the last migration batch.
     *
     * @param array $migrations
     *
     * @return array
     */
    public function getLast($migrations)
    {
        $query = $this->table()
            ->where('batch', $this->getLastBatchNumber($migrations))
            ->whereIn('migration', $migrations)
            ;

        $result = $query->orderBy('migration', 'desc')->get();

        return collect($result)->map(function ($item) {
            return (array) $item;
        })->lists('migration');
    }

    /**
     * Get the ran migrations.
     *
     * @return array
     */
    public function getRan()
    {
        return $this->table()->lists('migration');
    }
}
