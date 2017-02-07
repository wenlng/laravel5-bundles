<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-26
 * Time: 14:31
 */

namespace Awen\Bundles\Supports;

class Stub
{
    /**
     * The stub path.
     *
     * @var string
     */
    protected $path;

    /**
     * The base path of stub file.
     *
     * @var null|string
     */
    protected static $basePath = null;

    /**
     * The replacements array.
     *
     * @var array
     */
    protected $replaces = [];

    public function __construct($path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }

    /**
     * Set stub path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get stub path.
     *
     * @return string
     */
    public function getPath()
    {
        return static::getBasePath().$this->path;
    }

    /**
     * Set base path.
     *
     * @param string $path
     */
    public static function setBasePath($path)
    {
        static::$basePath = $path;
    }

    /**
     * Get base path.
     *
     * @return string|null
     */
    public static function getBasePath()
    {
        return static::$basePath;
    }

    /**
     * Get stub contents.
     *
     * @return mixed|string
     */
    public function getContents()
    {
        $contents = file_get_contents($this->getPath());

        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('%'.strtoupper($search).'%', $replace, $contents);
        }

        return $contents;
    }

    /**
     * Get stub contents.
     *
     * @return string
     */
    public function render()
    {
        return $this->getContents();
    }

     /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
