<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\MasterProduct as MasterProductBase;

class MasterProduct extends MasterProductBase
{
    use HasTenant;
}