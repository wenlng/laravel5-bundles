<?php

/**
 * Author: liangwengao
 * Email: wengaolng@gmail.com
 * Date: 2017-01-25
 * Time: 12:57
 */

namespace Awen\Bundles\Facades;

use Illuminate\Support\Facades\Facade;

class Bundles extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bundles';
    }
}