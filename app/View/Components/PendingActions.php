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

class PendingActions extends Component
{
    public Collection $pendingActions;

    public function __construct(?YnhServer $server = null)
    {
        if (isset($server->id)) {
            $servers = collect([$server]);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $servers = YnhServer::forUser($user);
        }
        $this->pendingActions = $servers
            ->flatMap(fn(YnhServer $server) => $server->pendingActions())
            ->sortBy([
                fn(YnhSshTraces $a, YnhSshTraces $b) => $a->updated_at->diffInMilliseconds($b->updated_at),
                fn(YnhSshTraces $a, YnhSshTraces $b) => strcmp($a->server->name, $b->server->name),
            ]);
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.pending-actions');
    }
}
