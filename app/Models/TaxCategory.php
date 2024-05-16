<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Taxes\Models\TaxCategory as TaxCategoryBase;

class TaxCategory extends TaxCategoryBase
{
    use HasTenant;
}