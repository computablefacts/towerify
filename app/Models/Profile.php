<?php

namespace App\Models;

use App\Traits\HasTenant;
use Konekt\User\Models\Profile as ProfileBase;

class Profile extends ProfileBase
{
    use HasTenant;
}