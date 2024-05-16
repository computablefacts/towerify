<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\Customer as CustomerBase;

class Customer extends CustomerBase
{
    use HasTenant;
}