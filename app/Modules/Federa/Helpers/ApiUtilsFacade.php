<?php

namespace App\Modules\Federa\Helpers;

use Illuminate\Support\Facades\Facade;

class ApiUtilsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'f_api_utils';
    }
}