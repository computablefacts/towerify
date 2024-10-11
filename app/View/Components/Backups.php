<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Backups extends Component
{
    public array $backups;
    public ?string $url;

    public function __construct(?YnhServer $server = null)
    {
        if (isset($server->id)) {
            $this->backups = $server->backups->all();
            $this->url = route('ynh.servers.create-backup', $server);
        } else {
            /** @var User $user */
            $user = Auth::user();
            $this->backups = YnhServer::forUser($user)->flatMap(fn(YnhServer $server) => $server->backups)->all();
            $this->url = null;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.backups');
    }
}
