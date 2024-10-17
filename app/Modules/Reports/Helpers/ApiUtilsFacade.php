<?php

namespace App\Modules\Reports\Helpers;

use Illuminate\Support\Facades\Facade;

class ApiUtilsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 're_api_utils';
    }
}