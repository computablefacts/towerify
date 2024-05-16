<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Adjustments\Models\Adjustment as AdjustmentBase;

class Adjustment extends AdjustmentBase
{
    use HasTenant;
}