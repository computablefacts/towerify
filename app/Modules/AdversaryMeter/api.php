<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'public',
], function () {
    Route::post('alert/{alert}/mark-and-check-again', 'CyberTodoController@markAsResolved');
    Route::get('vulnerabilities/{hash}', 'CyberTodoController@vulns');
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
    Route::get('screenshot/{screenshot}', 'AssetController@screenshot');
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
    Route::delete('assets/{asset}', 'AssetController@deleteAsset');
    Route::post('assets/restart/{asset}', 'AssetController@restartScan');
    Route::get('infos-from-asset/{asset}', 'AssetController@infosFromAsset');
    Route::get('attacker-index', 'HoneypotController@attackerIndex');
    Route::get('recent-events', 'HoneypotController@recentEvents');
    Route::get('blacklist-ips/{attackerId?}', 'HoneypotController@blacklistIps');
    Route::get('vulnerabilities/{attackerId?}', 'HoneypotController@getVulnerabilitiesWithAssetInfo');
    Route::get('vulnerabilities2/{asset}', 'HoneypotController@getVulnerabilitiesWithAssetInfo2');
    Route::get('activity/{attacker}', 'HoneypotController@attackerActivity');
    Route::get('profile/{attacker}', 'HoneypotController@attackerProfile');
    Route::get('profile/stats/{attacker}', 'HoneypotController@attackerStats');
    Route::get('profile/tools/{attacker}', 'HoneypotController@attackerTools');
    Route::get('profile/competency/{attacker}', 'HoneypotController@attackerCompetency');
    Route::get('last/events/{attackerId?}', 'HoneypotController@getMostRecentEvent');
    Route::get('last/honeypots', 'HoneypotController@lastHoneypots');
    Route::get('honeypots/stats/{honeypot}', 'HoneypotController@getHoneypotEventStats');
    Route::get('alerts/stats', 'HoneypotController@getAlertStats');
    Route::get('honeypots/status', 'HoneypotController@honeypotsStatus');
    Route::get('assets/tags', 'HoneypotController@assetTags');
    Route::get('hashes', 'HoneypotController@getHashes');
    Route::post('hashes', 'HoneypotController@createHash');
    Route::delete('hashes/{assetHashTag}', 'HoneypotController@deleteHash');
    Route::post('hidden-alerts', 'HoneypotController@createHiddenAlert');
    Route::delete('hidden-alerts/{hiddenAlert}', 'HoneypotController@deleteHiddenAlert');
    Route::post('honeypots', 'HoneypotController@postHoneypots');
    Route::post('honeypots/set-next-step', 'HoneypotController@moveHoneypotsConfigurationToNextStep');
});