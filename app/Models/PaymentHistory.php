<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Payment\Models\PaymentHistory as PaymentHistoryBase;

class PaymentHistory extends PaymentHistoryBase
{
    use HasTenant;
}