<?php

namespace App\Providers;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        VerifyCsrfToken::except(['cb/web/botman']);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
        Route::middleware('web')
            ->prefix('am/web')
            ->namespace('App\Modules\AdversaryMeter\Http\Controllers')
            ->group(base_path('app/Modules/AdversaryMeter/web.php'));
        Route::middleware('web')
            ->prefix('cb/web')
            ->namespace('App\Modules\CyberBuddy\Http\Controllers')
            ->group(base_path('app/Modules/CyberBuddy/web.php'));
        Route::middleware('web')
            ->prefix('tcb/web')
            ->namespace('App\Modules\TheCyberBrief\Http\Controllers')
            ->group(base_path('app/Modules/TheCyberBrief/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
        Route::prefix('am/api/v2')
            ->middleware('api')
            ->namespace('App\Modules\AdversaryMeter\Http\Controllers')
            ->group(base_path('app/Modules/AdversaryMeter/api.php'));
        Route::prefix('cb/api/v2')
            ->middleware('api')
            ->namespace('App\Modules\CyberBuddy\Http\Controllers')
            ->group(base_path('app/Modules/CyberBuddy/api.php'));
    }
}
