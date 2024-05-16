<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Konekt\Address\Query\Zones;
use Vanilo\Foundation\Models\Address as AddressBase;

class Address extends AddressBase
{
    use HasTenant;

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_addresses', 'address_id', 'customer_id');
    }

    public function isInEu(): bool
    {
        return Zones::withAnyScope()
            ->theAddressBelongsTo($this)
            ->filter(function (Zone $zone) {
                return $zone->name === 'EU';
            })->isNotEmpty();
    }
}