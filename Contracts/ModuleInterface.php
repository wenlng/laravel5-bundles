<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 15:47
 */

namespace Awen\Bundles\Contracts;

interface ModuleInterface
{
    /**注册路由
     * @return array
     */
    public function registerRouteFiles();

    /**注册中间件
     * @return array
     */
    public function registerMiddlewareFiles();

    /**注册事件
     * @return array
     */
    public function registerEventFiles();

    /**注册subscribe
     * @return array
     */
    public function registerSubscribeFiles();

    /**注册别名
     * @return array
     */
    public function registerClassAliases();

    /**注册服务提供者
     * @return mixed
     */
    public function registerProviderFiles();

    /**初始化参数
     * @return mixed
     */
    public function registerParams();

    /**初始化命令行
     * @return mixed
     */
    public function registerConsoleFiles();

}