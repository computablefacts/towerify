<?php

namespace App\View\Components;

use App\Models\Asset;
use App\Models\YnhOsquery;
use App\Models\YnhServer;
use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class SuspiciousActivity extends Component
{
    public Collection $events;
    public Collection $metrics;
    public Collection $assetsDiscovered;

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $servers = YnhServer::forUser($user);
        $cutOffTime = Carbon::now()->subDay();
        $this->events = YnhOsquery::suspiciousEvents($servers, $cutOffTime);
        $this->metrics = YnhOsquery::suspiciousMetrics($servers, $cutOffTime);
        $this->assetsDiscovered = Asset::where('created_at', '>=', $cutOffTime)->orderBy('asset')->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.suspicious-activity');
    }
}
