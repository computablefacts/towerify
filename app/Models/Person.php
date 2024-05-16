<?php

namespace App\Models;

use App\Traits\HasTenant;
use Konekt\Address\Models\Person as PersonBase;

class Person extends PersonBase
{
    use HasTenant;
}