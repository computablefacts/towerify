<?php

namespace App\Modules\AdversaryMeter\Helpers;

use Illuminate\Support\Facades\Facade;

class ApiUtilsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'am_api_utils';
    }
}