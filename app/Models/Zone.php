<?php

namespace App\Models;

use App\Traits\HasTenant;
use Konekt\Address\Models\Zone as ZoneBase;

class Zone extends ZoneBase
{
    use HasTenant;
}