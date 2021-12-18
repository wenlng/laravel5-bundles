<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 15:47
 */

namespace Awen\Bundles\Contracts;

interface ServiceKernelInterface
{
    /**注册服务
     * @return array
     */
    static public function registerServices();

    /**获取服务
     * @param $name
     * @param null $default
     * @return mixed
     * @throws BundleException
     */
    public function getService($name, $default = null);

}