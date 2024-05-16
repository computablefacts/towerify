<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Links\Models\LinkGroup as LinkGroupBase;

class LinkGroup extends LinkGroupBase
{
    use HasTenant;
}