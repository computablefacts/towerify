<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Shipment\Models\Shipment as ShipmentBase;

class Shipment extends ShipmentBase
{
    use HasTenant;
}