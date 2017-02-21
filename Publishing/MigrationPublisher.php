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
    public function __construct($bundle)
    {
        $this->setBundle($bundle);
        parent::__construct();
    }

    /**
     * 获取资源PATH
     * @param $bundle_name
     * @return string
     */
    public function getTargetPath($bundle_name){
        return $this->migrationPath($bundle_name);
    }


    /**
     * 获取资module源路径
     * @param $bundle_name
     * @return string
     */
    public function getSourcePath($bundle_name){
        return  $this->getMigrationPath($bundle_name);
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

        $this->publishBundle($bundle_name);
        return;
    }




}