<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Order\Models\Billpayer as BillpayerBase;

class Billpayer extends BillpayerBase
{
    use HasTenant;
}