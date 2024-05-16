<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Links\Models\LinkGroupItem as LinkGroupItemBase;

class LinkGroupItem extends LinkGroupItemBase
{
    use HasTenant;
}