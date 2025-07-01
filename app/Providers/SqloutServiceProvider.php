<?php

namespace App\Providers;

use Baril\Sqlout\SqloutServiceProvider as ServiceProvider;
use Laravel\Scout\EngineManager;

class SqloutServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        app(EngineManager::class)->extend('sqlout', function () {
            return new SqloutEngine();
        });
    }
}
