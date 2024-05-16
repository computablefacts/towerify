<?php

namespace App\Traits;

use App\Models\Property;

trait HasCpuProperty
{
    public function cpu(): float
    {
        $cpu = $this->valueOfProperty(Property::CPU_SLUG);
        return $cpu ? $cpu->getCastedValue() : -1;
    }
}