<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Sajya\Server\Middleware\GzipCompress;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v2', 'as' => 'v2.'], function () {

    /** PUBLIC ENDPOINTS */
    Route::group(['prefix' => 'public', 'as' => 'public.'], function () {

        Route::get('/docs', function (Request $request) {
            if (Storage::exists('/api/docs.public.html')) {
                return response(Storage::get('/api/docs.public.html'))
                    ->header('Content-Type', 'text/html')
                    ->header('Cache-Control', 'public, max-age=3600');
            }
            return response()->json(['error' => 'Fichier HTML non trouvé.'], 404);

        })->name('rpc.docs');

        Route::rpc('/endpoint', [
            //
        ])
            ->name('rpc.endpoint')
            ->middleware([GzipCompress::class]);
    });

    /** PRIVATE ENDPOINTS */
    Route::group(['prefix' => 'private', 'as' => 'private.'], function () {

        Route::get('/whoami', fn(Request $request) => Auth::user())->name('whoami')
            ->middleware([\App\Http\Middleware\Authenticate::class]);

        Route::get('/docs', function (Request $request) {
            if (Storage::exists('/api/docs.private.html')) {
                return response(Storage::get('/api/docs.private.html'))
                    ->header('Content-Type', 'text/html')
                    ->header('Cache-Control', 'public, max-age=3600');
            }
            return response()->json(['error' => 'Fichier HTML non trouvé.'], 404);

        })->name('rpc.docs');

        Route::rpc('/endpoint', [
            \App\Http\Procedures\ApplicationsProcedure::class,
            \App\Http\Procedures\InvitationsProcedure::class,
            \App\Http\Procedures\ServersProcedure::class,
        ])
            ->name('rpc.endpoint')
            ->middleware([GzipCompress::class, \App\Http\Middleware\Authenticate::class]);
    });
});
