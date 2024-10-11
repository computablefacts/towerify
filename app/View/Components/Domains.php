<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Traversable;

class Domains extends Component
{
    public $domains;

    public function __construct(?YnhServer $server = null)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($server && (is_array($server) || $server instanceof Traversable) && count($server) > 0) {
            $this->domains = $server->domains();
        } else {
            $this->domains = YnhServer::forUser($user)->flatMap(fn(YnhServer $server) => $server->domains);
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.domains');
    }
}
