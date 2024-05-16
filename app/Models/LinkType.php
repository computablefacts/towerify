<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Links\Models\LinkType as LinkTypeBase;

class LinkType extends LinkTypeBase
{
    use HasTenant;
}