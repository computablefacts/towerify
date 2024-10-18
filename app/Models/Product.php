<?php

namespace App\Models;

use App\Traits\HasTenant;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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

    public function registerMediaConversions(Media $media = null): void
    {
        $shortname = shorten(static::class);
        $variants = config("vanilo.foundation.image.$shortname.variants");

        if (!is_array($variants)) {
            $variants = config('vanilo.foundation.image.variants', []);
        }
        foreach ($variants as $name => $settings) {

            $conversion = $this->addMediaConversion($name)
                ->background('#ffffff') // see https://github.com/spatie/laravel-medialibrary/issues/3502#issuecomment-2404160689
                ->keepOriginalImageFormat()
                ->fit(
                    Fit::from($settings['fit'] ?? Fit::Contain->value),
                    $settings['width'] ?? 250,
                    $settings['height'] ?? 250
                );

            if (!($settings['queued'] ?? false)) {
                $conversion->nonQueued();
            }
        }
    }
}