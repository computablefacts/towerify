<?php

namespace App\View\Components;

use App\Models\YnhOsquery;
use App\Models\YnhServer;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Servers extends Component
{
    public Collection $os_infos;
    public Collection $servers;
    public string $is_yunohost;

    public function __construct(string $type = null)
    {
        /** @var User $user */
        $user = Auth::user();
        $this->is_yunohost = $type === 'ynh';
        $this->servers = YnhServer::forUser($user)
            ->filter(fn(YnhServer $server) => !$this->is_yunohost || $server->isYunoHost());
        $this->os_infos = YnhOsquery::osInfos($this->servers)
            ->map(function ($osInfos) {
                return (object)[
                    'ynh_server_id' => $osInfos->ynh_server_id,
                    'os' => "{$osInfos->os} {$osInfos->major_version}.{$osInfos->minor_version} ({$osInfos->architecture})",
                ];
            })
            ->groupBy('ynh_server_id');
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.servers');
    }
}
