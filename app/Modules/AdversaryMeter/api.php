<?php

use App\Modules\AdversaryMeter\Models\Honeypot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'public',
], function () {
    Route::post('alert/{alert}/mark-and-check-again', 'CyberTodoController@markAsResolved');
    Route::get('vulnerabilities/{hash}', 'CyberTodoController@vulns');
    Route::post('honeypots/{dns}', function (string $dns, \Illuminate\Http\Request $request) {

        if (!$request->hasFile('data')) {
            return response('Missing attachment', 500)
                ->header('Content-Type', 'text/plain');
        }

        $file = $request->file('data');

        if (!$file->isValid()) {
            return response('Invalid attachment', 500)
                ->header('Content-Type', 'text/plain');
        }

        $honeypot = Honeypot::where('dns', $dns)->first();

        if (!$honeypot) {
            return response('Unknown honeypot', 500)
                ->header('Content-Type', 'text/plain');
        }

        $filename = $file->getClientOriginalName();
        $timestamp = Carbon::createFromFormat('Y.m.d_H.i.s', \Illuminate\Support\Str::substr($filename, \Illuminate\Support\Str::position($filename, '-access.') + 8, 19));
        $events = collect(implode(gzfile($file->getRealPath())))
            ->flatMap(fn(string $line) => json_decode(trim($line), true));

        if ($events->isEmpty()) {
            return response('ok (empty file)', 200)
                ->header('Content-Type', 'text/plain');
        }

        event(new \App\Modules\AdversaryMeter\Events\IngestHoneypotsEvents($timestamp, $dns, $events->toArray()));

        return response("ok ({$events->count()} events in file)", 200)
            ->header('Content-Type', 'text/plain');
    });
})->middleware(['auth', 'throttle:400,1']);

Route::group([
    'prefix' => 'inventory',
], function () {
    Route::post('assets/discover', 'AssetController@discover');
    Route::post('assets/discover/from/ip', 'AssetController@discoverFromIp');
    Route::post('assets', 'AssetController@saveAsset');
    Route::get('assets', 'AssetController@userAssets');
    Route::post('asset/{asset}/monitoring/begin', 'AssetController@assetMonitoringBegins');
    Route::post('asset/{asset}/monitoring/end', 'AssetController@assetMonitoringEnds');
})->middleware(['auth']);

Route::group([
    'prefix' => 'inbox',
], function () {
    Route::get('screenshot/{screenshot}', 'AssetController@screenshot');
})->middleware(['auth']);

Route::group([
    'prefix' => 'facts',
], function () {
    Route::post('{asset}/metadata', 'AssetController@addTag');
    Route::delete('{asset}/metadata/{assetTag}', 'AssetController@removeTag');
})->middleware(['auth']);

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
    Route::get('honeypots/stats/{dns}', 'HoneypotController@getHoneypotEventStats');
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
})->middleware(['auth']);