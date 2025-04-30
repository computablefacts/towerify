<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Facade;

class SupersetApiUtilsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 're_api_utils';
    }
}