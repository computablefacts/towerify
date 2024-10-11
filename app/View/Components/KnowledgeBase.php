<?php

namespace App\View\Components;

use App\Modules\CyberBuddy\Http\Controllers\CyberBuddyController;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class KnowledgeBase extends Component
{
    public Collection $files;

    public function __construct()
    {
        $this->files = (new CyberBuddyController())->files();
    }

    public function render(): View|Closure|string
    {
        return view('components.knowledge-base');
    }
}
