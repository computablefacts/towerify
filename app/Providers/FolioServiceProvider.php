<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Folio::path(resource_path('views/cywise/pages/public'))
            ->uri('/public');

        Folio::path(resource_path('views/cywise/pages/private'))
            ->uri('/private')
            ->middleware([
                '*' => [
                    'auth',
                    'verified',
                ],
            ]);
    }
}
