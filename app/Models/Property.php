<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Properties\Models\Property as PropertyBase;

class Property extends PropertyBase
{
    use HasTenant;

    /** @deprecated */
    const CPU_SLUG = 'cpu';
    /** @deprecated */
    const RAM_SLUG = 'ram';
    /** @deprecated */
    const STORAGE_SLUG = 'storage';
}