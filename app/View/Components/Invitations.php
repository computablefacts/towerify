<?php

namespace App\View\Components;

use App\Models\Invitation;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Invitations extends Component
{
    public Collection $invitations;

    public function __construct()
    {
        $this->invitations = Invitation::whereNull('user_id')->get();
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.invitations');
    }
}
