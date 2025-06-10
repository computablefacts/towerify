<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use App\Http\Procedures\AssetsProcedure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $procedure = new AssetsProcedure();

        $request->replace(['is_monitored' => true]);
        $nbMonitored = count($procedure->list($request)['assets'] ?? 0);

        $request->replace(['is_monitored' => false]);
        $nbMonitorable = count($procedure->list($request)['assets'] ?? 0);

        return view('cywise.iframes.dashboard', [
            'nb_monitored' => $nbMonitored,
            'nb_monitorable' => $nbMonitorable,
        ]);
    }
}
