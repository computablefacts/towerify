<?php

namespace App\View\Components;

use App\Models\Conversation;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Conversations extends Component
{
    public Collection $conversations;
    public int $nbPages;
    public int $pagesSize;
    public int $currentPage;

    public function __construct(int $currentPage, int $pagesSize = 25)
    {
        $this->conversations = Conversation::query()
            ->where('dom', '!=', '[]')
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id')
            ->forPage($currentPage <= 0 ? 1 : $currentPage, $pagesSize <= 0 ? 25 : $pagesSize)
            ->get();
        $this->nbPages = ceil(Conversation::count() / $pagesSize);
        $this->currentPage = $currentPage;
        $this->pagesSize = $pagesSize;
    }

    public function render(): View|Closure|string
    {
        return view('components.conversations');
    }
}
