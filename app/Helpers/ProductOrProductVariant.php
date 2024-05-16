<?php

namespace App\Helpers;

use App\Models\MasterProductVariant;
use App\Models\Product;
use App\Models\Taxonomy;
use App\Traits\HasCpuProperty;
use App\Traits\HasRamProperty;
use App\Traits\HasStorageProperty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Vanilo\Category\Contracts\Taxon;
use Vanilo\Category\Traits\HasTaxons;
use Vanilo\Foundation\Models\MasterProductVariant as MasterProductVariantBase;
use Vanilo\Foundation\Models\Product as ProductBase;
use Vanilo\Properties\Contracts\Property;
use Vanilo\Properties\Contracts\PropertyValue;
use Vanilo\Properties\Traits\HasPropertyValues;
use Vanilo\Support\Traits\BuyableModel;
use Vanilo\Support\Traits\HasImagesFromMediaLibrary;

class ProductOrProductVariant
{
    use HasTaxons, HasPropertyValues, BuyableModel, HasImagesFromMediaLibrary;
    use HasCpuProperty, HasRamProperty, HasStorageProperty;

    private ?Product $product;
    private ?MasterProductVariant $masterProductVariant;

    public static function create(MasterProductVariantBase|ProductBase $product): ProductOrProductVariant
    {
        $p = new ProductOrProductVariant();
        if ($product instanceof MasterProductVariant) {
            $p->product = null;
            $p->masterProductVariant = $product;
            return $p;
        }
        if ($product instanceof Product) {
            $p->product = $product;
            $p->masterProductVariant = null;
            return $p;
        }
        if ($product instanceof MasterProductVariantBase) {
            $p->product = null;
            $p->masterProductVariant = self::cast1($product);
            return $p;
        }
        if ($product instanceof ProductBase) {
            $p->product = self::cast2($product);
            $p->masterProductVariant = null;
            return $p;
        }
        throw new \Exception('invalid class type');
    }

    /**
     * I posted an issue: https://github.com/vanilophp/framework/issues/166
     *
     * There is probably a bug in the framework because here $product is of type
     * Vanilo\Foundation\Models\Product instead of being of type App\Models\Product
     * despite being properly aliased in AppServiceProvider :-(
     *
     * @deprecated
     */
    private static function cast1(MasterProductVariantBase $product): MasterProductVariant
    {
        return MasterProductVariant::where('id', $product->id)->first();
    }

    /**
     * I posted an issue: https://github.com/vanilophp/framework/issues/166
     *
     * There is probably a bug in the framework because here $product is of type
     * Vanilo\Foundation\Models\Product instead of being of type App\Models\Product
     * despite being properly aliased in AppServiceProvider :-(
     *
     * @deprecated
     */
    private static function cast2(ProductBase $product): Product
    {
        return Product::where('id', $product->id)->first();
    }

    /********************************************************************************
     * HasTaxons
     ********************************************************************************/

    public function taxons(): MorphToMany
    {
        return $this->product ? $this->product->taxons() : $this->masterProductVariant->masterProduct->taxons();
    }

    public function addTaxon(Taxon $taxon): void
    {
        if ($this->product) {
            $this->product->addTaxon($taxon);
        } else {
            $this->masterProductVariant->masterProduct->addTaxon($taxon);
        }
    }

    public function addTaxons(iterable $taxons)
    {
        if ($this->product) {
            return $this->product->addTaxons($taxons);
        }
        return $this->masterProductVariant->masterProduct->addTaxons($taxons);
    }

    public function removeTaxon(Taxon $taxon)
    {
        if ($this->product) {
            return $this->product->removeTaxon($taxon);
        }
        return $this->masterProductVariant->masterProduct->removeTaxon($taxon);
    }

    /********************************************************************************
     * HasPropertyValues
     ********************************************************************************/

    public function assignPropertyValue(string|Property $property, mixed $value): void
    {
        if ($this->product) {
            $this->product->assignPropertyValue($property, $value);
        } else {
            $this->masterProductVariant->assignPropertyValue($property, $value);
        }
    }

    public function assignPropertyValues(iterable $propertyValues): void
    {
        if ($this->product) {
            $this->product->assignPropertyValues($propertyValues);
        } else {
            $this->masterProductVariant->assignPropertyValues($propertyValues);
        }
    }

    public function valueOfProperty(string|Property $property): ?PropertyValue
    {
        if ($this->product) {
            return $this->product->valueOfProperty($property);
        }
        return $this->masterProductVariant->valueOfProperty($property);
    }

    public function propertyValues(): MorphToMany
    {
        if ($this->product) {
            return $this->product->propertyValues();
        }
        return $this->masterProductVariant->propertyValues();
    }

    public function addPropertyValue(PropertyValue $propertyValue): void
    {
        if ($this->product) {
            $this->product->addPropertyValue($propertyValue);
        } else {
            $this->masterProductVariant->addPropertyValue($propertyValue);
        }
    }

    public function addPropertyValues(iterable $propertyValues)
    {
        if ($this->product) {
            return $this->product->addPropertyValues($propertyValues);
        }
        return $this->masterProductVariant->addPropertyValues($propertyValues);
    }

    public function removePropertyValue(PropertyValue $propertyValue)
    {
        if ($this->product) {
            return $this->product->removePropertyValue($propertyValue);
        }
        return $this->masterProductVariant->removePropertyValue($propertyValue);
    }

    /********************************************************************************
     * HasImagesFromMediaLibrary
     ********************************************************************************/

    public function hasImage(): bool
    {
        if ($this->product) {
            return $this->product->hasImage();
        }
        if ($this->masterProductVariant->hasImage()) {
            return $this->masterProductVariant->hasImage();
        }
        return $this->masterProductVariant->masterProduct->hasImage();
    }

    public function imageCount(): int
    {
        if ($this->product) {
            return $this->product->imageCount();
        }
        if ($this->masterProductVariant->hasImage()) {
            return $this->masterProductVariant->imageCount();
        }
        return $this->masterProductVariant->masterProduct->imageCount();
    }

    public function getThumbnailUrl(): ?string
    {
        if ($this->product) {
            return $this->product->getThumbnailUrl();
        }
        if ($this->masterProductVariant->hasImage()) {
            return $this->masterProductVariant->getThumbnailUrl();
        }
        return $this->masterProductVariant->masterProduct->getThumbnailUrl();
    }

    public function getThumbnailUrls(): Collection
    {
        if ($this->product) {
            return $this->product->getThumbnailUrls();
        }
        if ($this->masterProductVariant->hasImage()) {
            return $this->masterProductVariant->getThumbnailUrls();
        }
        return $this->masterProductVariant->masterProduct->getThumbnailUrls();
    }

    public function getImageUrl(string $variant = ''): ?string
    {
        if ($this->product) {
            return $this->product->getImageUrl($variant);
        }
        if ($this->masterProductVariant->hasImage()) {
            return $this->masterProductVariant->getImageUrl($variant);
        }
        return $this->masterProductVariant->masterProduct->getImageUrl($variant);
    }

    public function getImageUrls(string $variant = ''): Collection
    {
        if ($this->product) {
            return $this->product->getImageUrls($variant);
        }
        if ($this->masterProductVariant->hasImage()) {
            return $this->masterProductVariant->getImageUrls($variant);
        }
        return $this->masterProductVariant->masterProduct->getImageUrls($variant);
    }

    /********************************************************************************
     * BuyableModel
     ********************************************************************************/

    public function getId(): int|string
    {
        if ($this->product) {
            return $this->product->getId();
        }
        return $this->masterProductVariant->getId();
    }

    public function getName(): string
    {
        if ($this->product) {
            return $this->product->getName();
        }
        return $this->masterProductVariant->getName();
    }

    public function getPrice(): float
    {
        if ($this->product) {
            return $this->product->getPrice();
        }
        return $this->masterProductVariant->getPrice();
    }

    public function addSale(Carbon $date, int|float $units = 1): void
    {
        if ($this->product) {
            $this->product->addSale($date, $units);
        } else {
            $this->masterProductVariant->addSale($date, $units);
        }
    }

    public function removeSale(int|float $units = 1): void
    {
        if ($this->product) {
            $this->product->removeSale($units);
        } else {
            $this->masterProductVariant->removeSale($units);
        }
    }

    public function morphTypeName(): string
    {
        if ($this->product) {
            return $this->product->morphTypeName();
        }
        return $this->masterProductVariant->morphTypeName();
    }

    /********************************************************************************
     * Misc.
     ********************************************************************************/

    public function name(): string
    {
        return $this->getName();
    }

    public function sku(): string
    {
        return $this->product ? $this->product->sku : $this->masterProductVariant->sku;
    }

    public function product(): Product|MasterProductVariant
    {
        return $this->product ?: $this->masterProductVariant;
    }

    public function isServer(): bool
    {
        return $this->taxons()
            ->get()
            ->map(function (Taxon $taxon) {
                return $taxon->taxonomy()->first()->name;
            })
            ->contains(function (string $category) {
                return $category === Taxonomy::SERVERS;
            });
    }

    public function isApplication(): bool
    {
        return $this->taxons()
            ->get()
            ->map(function (Taxon $taxon) {
                return $taxon->taxonomy()->first()->name;
            })
            ->contains(function (string $category) {
                return $category === Taxonomy::APPLICATIONS || $category === Taxonomy::IT;
            });
    }
}