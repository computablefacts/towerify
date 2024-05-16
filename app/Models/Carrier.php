<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Shipment\Models\Carrier as CarrierBase;

class Carrier extends CarrierBase
{
    use HasTenant;
}