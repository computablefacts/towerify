<?php

namespace App\Models;

use App\Traits\HasTenant;
use Konekt\Address\Models\Organization as OrganizationBase;

class Organization extends OrganizationBase
{
    use HasTenant;
}