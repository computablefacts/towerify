<?php

namespace App\View\Components;

use App\Models\YnhServer;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Shell extends Component
{
    public YnhServer $server;

    public function __construct(YnhServer $server)
    {
        $this->server = $server;
    }

    public function render(): View|Closure|string
    {
        return view('components.shell');
    }
}
