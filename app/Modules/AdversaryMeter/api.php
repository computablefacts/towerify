<?php

use Illuminate\Support\Facades\Route;

/** @deprecated */
Route::middleware('auth:api')->get('/user', function () {
    return \Illuminate\Support\Facades\Auth::user();
});

Route::group([
    'prefix' => 'inventory',
], function () {
    Route::post('assets/discover', 'InventoryController@discover');
    Route::post('assets/discover/from/ip', 'InventoryController@discoverFromIp');
    Route::post('assets', 'InventoryController@saveAsset');
    Route::get('assets', 'InventoryController@userAssets');
    Route::post('asset/{id}/monitoring/begin', 'InventoryController@assetMonitoringBegins');
    Route::post('asset/{id}/monitoring/end', 'InventoryController@assetMonitoringEnds');
});