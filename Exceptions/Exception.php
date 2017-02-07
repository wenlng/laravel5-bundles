<?php

/**
 * Author: liangwengao
 * Email: 871024608@qq.com
 * Date: 2016-01-27
 * Time: 18:02
 */

namespace Awen\Bundles\Exceptions;

use Illuminate\Foundation\Application;

class Exception extends \Exception
{
    protected $prompt_message = 'zh';

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $app = Application::getInstance();
        $this->prompt_message = $app['config']->get('bundles.prompt_message');

        if($this->prompt_message =='zh') header("Content-type: text/html; charset=utf-8");

        if($this->prompt_message == 'en'){
            $message = $message['en'];
        }else{
            $message = $message['zh'];
        }

       // $message = '<Code:'. $code. '> ' .$message;

        parent::__construct($message, $code, $previous);
    }
}
