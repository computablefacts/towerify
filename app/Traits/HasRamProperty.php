<?php

namespace App\Traits;

use App\Models\Property;

trait HasRamProperty
{
    public function ram(): float
    {
        $ram = $this->valueOfProperty(Property::RAM_SLUG);
        return $ram ? $ram->getCastedValue() : -1;
    }
}