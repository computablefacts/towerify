<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\CartItem as CartItemBase;

class CartItem extends CartItemBase
{
    use HasTenant;
}