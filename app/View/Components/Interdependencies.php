<?php

namespace App\View\Components;

use App\Models\YnhNginxLogs;
use App\Models\YnhServer;
use App\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Traversable;

class Interdependencies extends Component
{
    public array $interdependencies;

    public function __construct(?YnhServer $server = null)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($server && (is_array($server) || $server instanceof Traversable) && count($server) > 0) {
            $this->interdependencies = YnhNginxLogs::interdependencies(YnhServer::forUser($user), $server);
        } else {
            $this->interdependencies = YnhNginxLogs::interdependencies(YnhServer::forUser($user));
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.interdependencies');
    }
}
