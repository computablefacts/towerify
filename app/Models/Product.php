<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Foundation\Models\Product as ProductBase;

class Product extends ProductBase
{
    use HasTenant;

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('default')
            ->useDisk(config('filesystems.images'));
    }
}