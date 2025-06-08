<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Domains extends Component
{
    public Collection $domains;

    public function __construct(?YnhServer $server = null)
    {
        if (isset($server->id)) {
            $this->domains = $server->domains->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $this->domains = YnhServer::forUser($user)
                ->flatMap(fn(YnhServer $server) => $server->domains())
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        }
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.domains');
    }
}
