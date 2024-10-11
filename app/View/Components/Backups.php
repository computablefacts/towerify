<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Backups extends Component
{
    public Collection $backups;
    public ?string $url;

    public function __construct(?YnhServer $server = null)
    {
        if (isset($server->id)) {
            $this->backups = $server->backups->sortByDesc('updated_at');
            $this->url = route('ynh.servers.create-backup', $server);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $this->backups = YnhServer::forUser($user)
                ->flatMap(fn(YnhServer $server) => $server->backups)
                ->sortByDesc('updated_at');
            $this->url = null;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.backups');
    }
}
