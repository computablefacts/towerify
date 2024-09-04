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
    Route::post('asset/{asset}/monitoring/begin', 'InventoryController@assetMonitoringBegins');
    Route::post('asset/{asset}/monitoring/end', 'InventoryController@assetMonitoringEnds');
});

Route::group([
    'prefix' => 'inbox',
], function () {
    Route::get('screenshot/{id}', 'InventoryController@screenshot');
});

Route::group([
    'prefix' => 'facts',
], function () {
    Route::post('{asset}/metadata', 'InventoryController@addTag');
    Route::delete('{asset}/metadata/{assetTag}', 'InventoryController@removeTag');
});