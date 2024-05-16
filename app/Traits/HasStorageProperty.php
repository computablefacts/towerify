<?php

namespace App\Traits;

use App\Models\Property;

trait HasStorageProperty
{
    public function storage(): float
    {
        $disk = $this->valueOfProperty(Property::STORAGE_SLUG);
        return $disk ? $disk->getCastedValue() : -1;
    }
}