<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Pagination extends Component
{
    public int $nbPages;
    public int $pagesSize;
    public int $currentPage;

    public function __construct(int $nbPages, int $pagesSize, int $currentPage)
    {
        $this->nbPages = $nbPages;
        $this->currentPage = $currentPage;
        $this->pagesSize = $pagesSize;
    }

    public function render(): View|Closure|string
    {
        return view('components.pagination');
    }
}
