<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Shipment\Models\ShippingMethod as ShippingMethodBase;

class ShippingMethod extends ShippingMethodBase
{
    use HasTenant;
}