<?php

use App\Modules\AdversaryMeter\Mail\AuditReport;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/cyber-todo/{hash}', 'CyberTodoController@show');

Route::get('/audit-report', function () {

    $alerts = Asset::where('is_monitored', true)->get()->flatMap(fn(Asset $asset) => $asset->alerts()->get());
    $alertsHigh = $alerts->filter(fn(Alert $alert) => $alert->level === 'High');
    $alertsMedium = $alerts->filter(fn(Alert $alert) => $alert->level === 'Medium');
    $alertsLow = $alerts->filter(fn(Alert $alert) => $alert->level === 'Low');
    $assetsMonitored = Asset::where('is_monitored', true)->orderBy('asset')->get();
    $assetsNotMonitored = Asset::where('is_monitored', false)->orderBy('asset')->get();
    $assetsDiscovered = Asset::where('created_by', '>=', Carbon::now()->subDay())->orderBy('asset')->get();

    return new AuditReport($alertsHigh, $alertsMedium, $alertsLow, $assetsMonitored, $assetsNotMonitored, $assetsDiscovered);
})->middleware('auth');