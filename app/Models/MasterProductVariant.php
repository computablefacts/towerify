<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\MasterProductVariant as MasterProductVariantBase;

class MasterProductVariant extends MasterProductVariantBase
{
    use HasTenant;
}