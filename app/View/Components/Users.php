<?php

namespace App\View\Components;

use App\Models\YnhServer;
use App\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Users extends Component
{
    public ?YnhServer $server;
    public Collection $users;

    public function __construct(?YnhServer $server = null)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->tenant_id) {
            $this->users = User::where('is_active', true)->get()->sortBy('fullname', SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            $users = User::where('is_active', true)->where('tenant_id', $user->tenant_id);
            if ($user->customer_id) {
                $users = $users->where('customer_id', $user->customer_id);
            }
            $this->users = $users->get()->sortBy('fullname', SORT_NATURAL | SORT_FLAG_CASE);
        }
        $this->server = null;
    }

    public function render(): View|Closure|string
    {
        return view('components.users');
    }
}
