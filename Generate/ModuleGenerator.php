<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-01-25
 * Time: 16:56
 */

namespace Awen\Bundles\Generate;

class ModuleGenerator extends Generator
{
    public function __construct($name)
    {
        parent::__construct();
        $this->setModule($name);
    }
   
    /**
     * 获取module需要生成的目录
     * @return array
      */
    public function getModuleFolders()
    {
        return array_values($this->rootConfig('modules.generator.paths'));
    }

    /**
     * 生成模块目录
     */
    public function generateFolders()
    {
        foreach ($this->getModuleFolders() as $folder) {
            $path = $this->getModulePath() . '/' . $folder;
            if(!is_dir($path)) $this->filesystem->makeDirectory($path, 0755, true);
            $this->generateGitKeep($path);
        }
    }

    /**
     * 获取包的所有需要生成的文件
     * @return mixed
     */
    public function getModuleFiles()
    {
        return  $this->rootConfig('modules.generator.files');
    }

    /**查找将要替换的值
     * @param $stub
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->rootConfig('modules.replacements');
        return $this->_getReplacement($stub, $replacements);
    }

    /**
     * 获取模板内容，并替换内容中的变量
     * @param $stub
     * @return string
     */
    protected function getStubContents($stub)
    {
        return $this->createStub('/'.$stub.'.stub', $this->getReplacement($stub))->render();
    }

    /**
     * 替换文本中的变量
     * @param $stub
     * @param $str
     * @return mixed
     */
    protected function getStubStr($stub, $str)
    {
        foreach ($this->getReplacement($stub) as $search => $replace) {
            $str = str_replace('%'.strtoupper($search).'%', $replace, $str);
        }
        return $str;
    }

    /**
     * 生成Module文件
     */
    public function generateFiles(){
        foreach ($this->getModuleFiles() as $stub => $file) {
            $path = $this->getModulePath().'/'.$this->getStubStr($stub, $file);

            if (!$this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));
            $this->console->info("Created : {$path}");
        }
    }

    /**
     * 删除旧的
     * @return bool
     */
    public function deleteOld(){
        $module = $this->getModulePath();
        return $this->filesystem->deleteDirectory($module, true);
    }

    /**
     * 提示输入作者信息
     */
    public function inputAuthorInfo(){
        $default_name = $this->config('composer.module.author.name') ?: 'name';
        $default_email = $this->config('composer.module.author.email') ?: 'email';

        $this->author_name = $this->console->ask("Please input author name: ", $default_name);
        $this->author_email = $this->console->ask("Please input author email: ", $default_email);
    }

    /**
     * 生成默认例子文件
     */
    public function generateDefaultFiles(){
        $default_files = $this->rootConfig('modules.generator.default');
        $bundle_name = $this->getBundleName().$this->bundle_suffix;
        $module_name = $this->getModuleName().$this->module_suffix;

        foreach ($default_files as $stub => $file) {
            $path = $this->getModulePath().'/'.$this->getStubStr($stub, $file);
            if (!$this->filesystem->isDirectory($dir = dirname($path))) {
                $this->filesystem->makeDirectory($dir, 0775, true);
            }

            $this->filesystem->put($path, $this->getStubContents($stub));
            $this->console->info("Created Default : {$path}");
        }
        $this->console->info("----------------------------------------------------------------------------");
        $this->console->line("| Please register module <info>[{$module_name}]</info> in the bundle <info>[{$bundle_name}]</info>. |");
        $this->console->info("----------------------------------------------------------------------------");
    }


    /**
     * 生成Module
     */
    public function generate()
    {
        $bundle = $this->getBundleName();
        if(empty($bundle)){
            $this->console->error("Please appoint the bundle: -b BundleName!");
            return;
        }

        if(!$this->hasBundle()){
            $this->console->error("The bundle: [{$bundle}] not exist  in the bundle: [{$bundle}]!");
            return;
        }

        $name = $this->getModuleName();
        if ($this->hasModule()) {
            if ($this->force) {
                if ($this->force) {
                    $this->console->warn("The module: [{$name}] already exist in the bundle: [{$bundle}]!");
                    $confirm = $this->console->confirm("Are you sure, the old module: [{$name}] will clear in the bundle: [{$bundle}]!");
                    if (!$confirm) return;

                    $module = $this->app_kernel->getModule($bundle, $name);
                    if($module)
                        $module->delete();
                    else
                        $this->deleteOld();
                }
            } else {
                $this->console->error("The module: [{$name}] already exist in the bundle: [{$bundle}]!");
                return;
            }
        }

        $this->inputAuthorInfo();
        $this->generateFolders();
        $this->generateFiles();

        if(!$this->clean){
            $this->generateDefaultFiles();
        }

        $this->console->info("Then Module [{$name}] created successfully in the Bundle [{$bundle}].");
    }

    /**
     * 获取module名
     * @return mixed
     */
    protected function getModuleNameReplacement()
    {
        return $this->getModuleName() . $this->module_suffix;
    }


    /**
     * 获取module小写名
     * @return mixed
     */
    protected function getModuleLowerNameReplacement()
    {
        return $this->getLowerModuleName();
    }

    /**
     * 获取module大写名
     * @return string
     */
    protected function getModuleStudlyNameReplacement()
    {
        return $this->getModuleName();
    }

    /**
     * 获取bundle小写名
     * @return mixed
     */
    protected function getBundleLowerNameReplacement()
    {
        return $this->getLowerBundleName();
    }

    /**
     * 获取bundle名
     * @return mixed
     */
    protected function getBundleStudlyNameReplacement()
    {
        return $this->getBundleName();
    }

    /**
     * 获取Bundle命名字符串
     * @return string
     */
    protected function getBundleNamespaceStrReplacement()
    {
        $bas_namespace = str_replace('\\', '\\\\', $this->rootConfig('namespace'));
        return $bas_namespace . '\\\\' . $this->getBundleName();
    }

    /**
     * 获取module小写名
     * @return mixed
     */
    protected function getModuleLowerNotNameReplacement()
    {
        return strtolower($this->getModuleName());
    }

    /**
     * 获取bundle小写名
     * @return mixed
     */
    protected function getBundleLowerNotNameReplacement()
    {
        return strtolower($this->getBundleName());
    }

    /**
     * 获取api_router文件
     * @return mixed
     */
    protected function getApiRouteFileReplacement(){
        return $this->rootConfig('modules.generator.files.api_route');
    }

    /**
     * 获取view_router文件
     * @return mixed
     */
    protected function getViewRouteFileReplacement(){
        return $this->rootConfig('modules.generator.files.view_route');
    }

    /**
     * 获取api_router文件
     * @return mixed
     */
    protected function getApiControllerNamespaceReplacement(){
        return str_replace('/', '\\', $this->rootConfig('modules.generator.paths.api_controller')) ;
    }

    /**
     * 获取view_router文件
     * @return mixed
     */
    protected function getViewControllerNamespaceReplacement(){
        return str_replace('/', '\\', $this->rootConfig('modules.generator.paths.view_controller'));
    }

    /**
     * 获取view_router路径
     * @return mixed
     */
    protected function getViewControllerPathReplacement(){
        return $this->rootConfig('modules.generator.paths.view_controller');
    }

    /**
     * 获取第三方名称
     * @return mixed
     */
    protected function getModuleVendorReplacement()
    {
        return $this->config('composer.module.vendor');
    }

    /**
     * 获取作者名称
     * @return mixed
     */
    protected function getModuleAuthorNameReplacement()
    {
        return $this->author_name;
    }

    /**
     * 获取作者邮箱
     * @return mixed
     */
    protected function getModuleAuthorEmailReplacement()
    {
        return $this->author_email;
    }

    /**
     * 获取Bundle命名
     * @return string
     */
    protected function getModuleNamespaceReplacement()
    {
        return $this->getModuleCurrentNamespace();
    }

    /**
     * 获取Bundle命名字符串
     * @return string
     */
    protected function getModuleNamespaceStrReplacement()
    {
        $bas_namespace = str_replace('\\', '\\\\', $this->rootConfig('namespace'));
        return $bas_namespace . '\\\\'. $this->getBundleName() .'\\\\'.  $this->getModuleNamespace().'\\\\'. $this->getModuleName();
    }

    /**
     * 获取中间件命名
     * @return string
     */
    protected function getMiddlewareNamespaceReplacement(){
        $module_namespace = $this->getModuleCurrentNamespace();
            $middleware_namespace = str_replace('/', '\\', $this->rootConfig('modules.generator.paths.middleware'));

        return $module_namespace . '\\' .$middleware_namespace;
    }

    /**
     * 获取队列命名
     * @return string
     */
    protected function getJobNamespaceReplacement(){
        $module_namespace = $this->getModuleCurrentNamespace();
        $middleware_namespace = str_replace('/', '\\', $this->rootConfig('modules.generator.paths.job'));

        return $module_namespace . '\\' .$middleware_namespace;
    }

    /**
     * 获取服务提供命名
     * @return string
     */
    protected function getProviderNamespaceReplacement(){
        $module_namespace = $this->getModuleCurrentNamespace();
            $provider_namespace = str_replace('/', '\\', $this->rootConfig('modules.generator.paths.provider'));

        return $module_namespace . '\\' .$provider_namespace;
    }

    /**
     * 获取Seed命名
     * @return string
     */
    protected function getSeedNameReplacement(){
        return $this->getModuleName() . 'DatabaseSeeder';
    }

    /**
     * 获取Seed命名
     * @return string
     */
    protected function getSeedNamespaceReplacement(){

        $module_namespace = $this->getModuleCurrentNamespace();
        $provider_namespace = str_replace('/', '\\', $this->rootConfig('modules.generator.paths.seeder'));

        return $module_namespace . '\\' .$provider_namespace;
    }


}