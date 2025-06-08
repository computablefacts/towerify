<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tables extends Component
{
    public function render(): View|Closure|string
    {
        return view('cywise.components.tables');
    }
}
