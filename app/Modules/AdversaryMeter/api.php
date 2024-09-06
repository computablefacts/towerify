<?php

use Illuminate\Support\Facades\Route;

/** @deprecated */
Route::middleware('auth:api')->get('/user', function () {
    return \Illuminate\Support\Facades\Auth::user();
});

Route::group([
    'prefix' => 'inventory',
], function () {
    Route::post('assets/discover', 'AssetController@discover');
    Route::post('assets/discover/from/ip', 'AssetController@discoverFromIp');
    Route::post('assets', 'AssetController@saveAsset');
    Route::get('assets', 'AssetController@userAssets');
    Route::post('asset/{asset}/monitoring/begin', 'AssetController@assetMonitoringBegins');
    Route::post('asset/{asset}/monitoring/end', 'AssetController@assetMonitoringEnds');
});

Route::group([
    'prefix' => 'inbox',
], function () {
    Route::get('screenshot/{id}', 'AssetController@screenshot');
});

Route::group([
    'prefix' => 'facts',
], function () {
    Route::post('{asset}/metadata', 'AssetController@addTag');
    Route::delete('{asset}/metadata/{assetTag}', 'AssetController@removeTag');
});

Route::group([
    'prefix' => 'adversary',
], function () {
    Route::get('infos-from-asset/{asset}', 'AssetController@infosFromAsset');
    Route::get('attacker-index', 'HoneypotController@attackerIndex');
    Route::get('recent-events', 'HoneypotController@recentEvents');
});