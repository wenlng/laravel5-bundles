<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

use Awen\Bundles\Repositories\ResourcesRepository;

abstract class Generator extends ResourcesRepository
{
    /**
     * 如果Bundle存在是否强制重新生成
     * @var bool
     */
    protected $force = false;

    /**
     * 默认生成一个简单实例，是否去掉实例
     * @var bool
     */
    protected $clean = false;

    /**
     * 作者信息
     * @var string
     */
    protected $author_name = '';
    protected $author_email = '';

    /**
     * 设置强制刷新
     * @param $force
     * @return $this
     */
    public function setForce($force)
    {
        $this->force = $force;
        return $this;
    }

    /**
     * 设置不要实例
     * @param $clean
     * @return $this
     */
    public function setClean($clean)
    {
        $this->clean = $clean;
        return $this;
    }


    /**
     * 查找将要替换的值
     * @param $stub
     * @param $replacements
     * @return array
     */
    public function _getReplacement($stub, $replacements)
    {
        if (empty($stub) || !isset($replacements[$stub])) return [];

        $keys = $replacements[$stub];
        $replaces = [];

        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get' . ucfirst(studly_case(strtolower($key))) . 'Replacement')) {
                $replaces[$key] = call_user_func([$this, $method]);
            } else {
                $replaces[$key] = null;
            }
        }

        return $replaces;
    }

    /**
     * 因为git不允许全空目录提交至版本库，所以生成一个空文件防止添加不到版本库
     * @param string $path
     */
    public function generateGitKeep($path)
    {
        $this->filesystem->put($path . '/.gitkeep', '');
    }

    /**
     * 获取Bundle当前命名
     * @return mixed
     */
    protected function getBundleCurrentNamespace()
    {
        return $this->rootConfig('namespace') . '\\' . $this->getBundleName();
    }

    
}