<?php

namespace App\View\Components;

use App\Jobs\Summarize;
use App\Models\YnhServer;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

/** @deprecated */
class Overview extends Component
{
    public array $numberOfVulnerabilitiesByLevel;
    public array $overview;

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $servers = YnhServer::forUser($user);
        $this->numberOfVulnerabilitiesByLevel = Summarize::numberOfVulnerabilitiesByLevel();
        $this->overview = [
            'servers_monitored' => Summarize::monitoredServers(),
            'ip_monitored' => Summarize::monitoredIps(),
            'dns_monitored' => Summarize::monitoredDns(),
            'metrics_collected' => Summarize::collectedMetrics($servers),
            'events_collected' => Summarize::collectedEvents($servers),
            'vulns_high' => $this->numberOfVulnerabilitiesByLevel['high'],
            'vulns_medium' => $this->numberOfVulnerabilitiesByLevel['medium'],
            'vulns_low' => $this->numberOfVulnerabilitiesByLevel['low'],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.overview');
    }
}
