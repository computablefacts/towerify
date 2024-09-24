<?php

namespace App\Modules\CyberBuddy\Helpers;

use Illuminate\Support\Facades\Facade;

class ApiUtilsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cb_api_utils';
    }
}