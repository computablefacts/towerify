<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TablesAdd extends Component
{
    public string $step;

    public function __construct(string $step)
    {
        $this->step = empty($step) ? '1' : $step;
    }

    public function render(): View|Closure|string
    {
        return view('cywise.components.tables-add');
    }
}
