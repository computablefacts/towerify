<?php

namespace App\View\Components;

use App\Models\Asset;
use App\Models\File;
use App\Models\Honeypot;
use App\Models\YnhServer;
use App\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Onboarding extends Component
{
    public bool $hasAssets;
    public bool $hasAgents;
    public bool $hasHoneypots;
    public bool $hasPssi;
    public bool $show;

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $this->hasAssets = Asset::exists();
        $this->hasAgents = YnhServer::forUser($user)->isNotEmpty();
        $this->hasHoneypots = Honeypot::exists();
        $this->hasPssi = File::exists();
        $this->show = !$this->hasAssets || !$this->hasAgents || !$this->hasHoneypots || !$this->hasPssi;
    }

    public function render(): View|Closure|string
    {
        return view('components.onboarding');
    }
}
