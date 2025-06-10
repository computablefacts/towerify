<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use App\Http\Procedures\AssetsProcedure;
use App\Http\Procedures\VulnerabilitiesProcedure;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $procedure = new AssetsProcedure();

        $request->replace(['is_monitored' => true]);
        $nbMonitored = count($procedure->list($request)['assets'] ?? []);

        $request->replace(['is_monitored' => false]);
        $nbMonitorable = count($procedure->list($request)['assets'] ?? []);

        $procedure = new VulnerabilitiesProcedure();

        $alerts = $procedure->list($request);
        $nbHigh = count($alerts['high'] ?? []);
        $nbMedium = count($alerts['medium'] ?? []);
        $nbLow = count($alerts['low'] ?? []);
        $todo = collect($alerts['high'] ?? [])
            ->concat($alerts['medium'] ?? [])
            ->concat($alerts['low'] ?? [])
            ->sortBy(function (Alert $alert) {
                if ($alert->level === 'High') {
                    return 1;
                }
                if ($alert->level === 'Medium') {
                    return 2;
                }
                if ($alert->level === 'Low') {
                    return 3;
                }
                return 4;
            })
            ->values()
            ->take(5);

        return view('cywise.iframes.dashboard', [
            'nb_monitored' => $nbMonitored,
            'nb_monitorable' => $nbMonitorable,
            'nb_high' => $nbHigh,
            'nb_medium' => $nbMedium,
            'nb_low' => $nbLow,
            'todo' => $todo,
        ]);
    }
}
