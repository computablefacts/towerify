<?php

namespace App\Models;

use App\Traits\HasTenant;
use Vanilo\Adjustments\Adjusters\SimpleTax;
use Vanilo\Taxes\Models\TaxRate as TaxRateBase;

class TaxRate extends TaxRateBase
{
    use HasTenant;

    public static function setOrUpdateEuVat(?\Vanilo\Cart\Contracts\Cart $cart): ?float
    {
        if ($cart) {
            $amount = TaxRate::updateEuVat($cart);
            if (!$amount) {
                $adjustment = $cart->adjustments()->create(TaxRate::euVat());
                return $adjustment->getAmount();
            }
            return $amount;
        }
        return null;
    }

    public static function updateEuVat(?\Vanilo\Cart\Contracts\Cart $cart): ?float
    {
        if ($cart) {
            foreach ($cart->adjustments() as $adjustment) {
                if ($adjustment->getTitle() === 'EU VAT') {
                    $cart->adjustments()->remove($adjustment);
                    $adjustment = $cart->adjustments()->create(TaxRate::euVat());
                    return $adjustment->getAmount();
                }
            }
        }
        return null;
    }

    private static function euVat(): SimpleTax
    {
        $zoneIsEu = Zone::where('name', 'EU')->first();
        $taxCategoryIsVat = TaxCategory::findByName('VAT');
        $taxRate = TaxRate::byTaxCategory($taxCategoryIsVat)
            ->where('zone_id', $zoneIsEu->id)
            ->first();
        $tax = new SimpleTax($taxRate->getRate());
        $tax->setTitle('EU VAT');
        return $tax;
    }
}