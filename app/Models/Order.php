<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\Order as OrderBase;

class Order extends OrderBase
{
    use HasTenant;
}