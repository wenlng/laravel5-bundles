<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2017-02-01
 * Time: 17:55
 */
namespace Awen\Bundles\publishing;

class MigrationPublisher extends Publisher
{
    public function __construct($bundle, $module)
    {
        $this->setBundle($bundle);
        $this->setModule($module);
        parent::__construct();
    }

    /**
     * 获取资源PATH
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    public function getTargetPath($bundle_name, $module_name){
        return $this->migrationPath($bundle_name, $module_name);
    }


    /**
     * 获取资module源路径
     * @param $bundle_name
     * @param $module_name
     * @return string
     */
    public function getSourcePath($bundle_name, $module_name){
        return  $this->getMigrationPath($bundle_name, $module_name);
    }

    /**
     * 更新资源
     */
    public function publish(){
        $bundle_name = $this->getBundleName();
        if(empty($bundle_name)){
            $this->console->error("Please appoint the bundle: -b BundleName!");
            return;
        }
        if (!$this->hasBundle()) {
            $this->console->error("The bundle: [{$bundle_name}] not exist!");
            return;
        }

        $module_name = $this->getModuleName();
        if(!empty($module_name)){
            if (!$this->hasModule()) {
                $this->console->error("The module: [{$module_name}] not exist!");
                return;
            }
        }

        if(!empty($bundle_name) && !empty($module_name)){
            $this->publishModule($bundle_name, $module_name);
        }else{
            $this->publishBundle($bundle_name);
        }

        return;
    }




}