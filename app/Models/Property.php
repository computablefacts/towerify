<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Properties\Models\Property as PropertyBase;

class Property extends PropertyBase
{
    use HasTenant;

    const CPU_SLUG = 'cpu';
    const RAM_SLUG = 'ram';
    const STORAGE_SLUG = 'storage';
}