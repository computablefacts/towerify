<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Collections extends Component
{
    public Collection $collections;
    public int $nbPages;
    public int $pagesSize;
    public int $currentPage;

    public function __construct(int $currentPage, int $pagesSize = 25)
    {
        $this->collections = \App\Models\Collection::query()
            ->where('is_deleted', false)
            ->where(function ($query) {
                $user = Auth::user();
                $query->where('name', "privcol{$user->id}")
                    ->orWhere('name', 'not like', "privcol%");
            })
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
        $this->nbPages = ceil(\App\Models\Collection::count() / $pagesSize);
        $this->currentPage = $currentPage;
        $this->pagesSize = $pagesSize;
    }

    public function render(): View|Closure|string
    {
        return view('components.collections');
    }
}
