<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Properties\Models\PropertyValue as PropertyValueBase;

class PropertyValue extends PropertyValueBase
{
    use HasTenant;
}