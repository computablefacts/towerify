<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Payment\Models\PaymentMethod as PaymentMethodBase;

class PaymentMethod extends PaymentMethodBase
{
    use HasTenant;
}