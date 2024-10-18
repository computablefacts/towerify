<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\Taxon as TaxonBase;

class Taxon extends TaxonBase
{
    use HasTenant;

    const SUBSCRIPTIONS = 'Subscriptions';
    const YUNOHOST = 'YunoHost';
    const YEARLY = 'Yearly';
    const MONTHLY = 'Monthly';
}