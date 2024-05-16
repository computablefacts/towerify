<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\Taxonomy as TaxonomyBase;

class Taxonomy extends TaxonomyBase
{
    use HasTenant;

    const IT = 'IT';
    const BUSINESS = 'Business';
    /** @deprecated */
    const APPLICATIONS = 'Applications';
    /** @deprecated */
    const SERVERS = 'Servers';
}