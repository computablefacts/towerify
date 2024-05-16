<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Channel\Models\Channel as ChannelBase;

class Channel extends ChannelBase
{
    use HasTenant;
}