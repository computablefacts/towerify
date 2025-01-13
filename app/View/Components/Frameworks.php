<?php

namespace App\View\Components;

use App\Models\YnhFramework;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Frameworks extends Component
{
    public Collection $frameworks;

    public function __construct()
    {
        $this->frameworks = YnhFramework::query()
            ->orderBy('provider')
            ->orderBy('name')
            ->get();
    }

    public function render(): View|Closure|string
    {
        return view('components.frameworks');
    }
}
