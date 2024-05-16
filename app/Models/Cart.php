<?php

namespace App\Models;

use App\Helpers\ProductOrProductVariant;
use App\Traits\HasTenant;
use Illuminate\Support\Collection;
use Vanilo\Foundation\Models\Cart as CartBase;

class Cart extends CartBase
{
    use HasTenant;

    public function getItems(): Collection
    {
        $items = $this->items;

        foreach ($items as $item) {
            $item->product = ProductOrProductVariant::create($item->product)->product();
        }
        return $items;
    }
}