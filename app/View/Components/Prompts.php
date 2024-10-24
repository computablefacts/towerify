<?php

namespace App\View\Components;

use App\Modules\CyberBuddy\Models\Prompt;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Prompts extends Component
{
    public Collection $prompts;
    public int $nbPages;
    public int $pagesSize;
    public int $currentPage;

    public function __construct(int $currentPage, int $pagesSize = 25)
    {
        $this->prompts = Prompt::select('cb_prompts.*')
            ->orderBy('name')
            ->forPage($currentPage <= 0 ? 1 : $currentPage, $pagesSize <= 0 ? 25 : $pagesSize)
            ->get();
        $this->nbPages = ceil(Prompt::count() / $pagesSize);
        $this->currentPage = $currentPage;
        $this->pagesSize = $pagesSize;
    }

    public function render(): View|Closure|string
    {
        return view('components.prompts');
    }
}
