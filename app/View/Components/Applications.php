<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Applications extends Component
{
    public Collection $apps;

    public function __construct(?YnhServer $server = null)
    {
        if (isset($server->id)) {
            $servers = collect([$server]);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $servers = YnhServer::forUser($user);
        }
        $this->apps = $servers
            ->flatMap(fn(YnhServer $server) => $server->applications)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.applications');
    }
}
