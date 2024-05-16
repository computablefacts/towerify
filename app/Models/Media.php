<?php

namespace App\Models;

use App\Traits\HasTenant;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaBase;

class Media extends MediaBase
{
    use HasTenant;
}