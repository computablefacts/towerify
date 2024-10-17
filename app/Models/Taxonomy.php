<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\Taxonomy as TaxonomyBase;

class Taxonomy extends TaxonomyBase
{
    use HasTenant;

    const YUNOHOST = 'YunoHost';
    /** @deprecated */
    const IT = 'IT';
    /** @deprecated */
    const BUSINESS = 'Business';
    /** @deprecated */
    const APPLICATIONS = 'Applications';
    /** @deprecated */
    const SERVERS = 'Servers';
}