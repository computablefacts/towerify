<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Payment\Models\Payment as PaymentBase;

class Payment extends PaymentBase
{
    use HasTenant;
}