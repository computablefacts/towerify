<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Tables extends Component
{
    public string $step;

    public function __construct(string $step)
    {
        $this->step = empty($step) ? '1' : $step;
    }

    public function render(): View|Closure|string
    {
        return view('components.tables');
    }
}
