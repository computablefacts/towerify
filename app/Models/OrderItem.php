<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\OrderItem as OrderItemBase;

class OrderItem extends OrderItemBase
{
    use HasTenant;
}