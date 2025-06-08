<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\Models\YnhSshTraces;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Traces extends Component
{
    public Collection $servers;
    public Collection $traces;
    public Collection $tracesGroupedByServers;

    public function __construct(?YnhServer $server = null)
    {
        if (isset($server->id)) {
            $this->servers = collect([$server]);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $this->servers = YnhServer::forUser($user);
        }
        $this->traces = $this->servers
            ->flatMap(fn(YnhServer $server) => $server->latestTraces())
            ->sortBy([
                fn(YnhSshTraces $a, YnhSshTraces $b) => strcmp($a->server->name, $b->server->name),
                fn(YnhSshTraces $a, YnhSshTraces $b) => $b->order - $a->order,
            ]);
        $this->tracesGroupedByServers = $this->traces->groupBy(fn($trace) => $trace->server->name);
        $this->servers = $this->servers->filter(fn(YnhServer $server) => isset($this->tracesGroupedByServers[$server->name]) && $this->tracesGroupedByServers[$server->name]->isNotEmpty());
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.traces');
    }
}
