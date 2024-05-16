<?php

namespace App\Models;

use App\Traits\HasTenant;
use Konekt\Address\Models\ZoneMember as ZoneMemberBase;

class ZoneMember extends ZoneMemberBase
{
    use HasTenant;
}