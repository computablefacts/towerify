<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CollectionsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $page = $params['page'] ?? 1;
        $pagesSize = $params['page_size'] ?? 25;
        $collections = \App\Models\Collection::query()
            ->where('is_deleted', false)
            ->where(function ($query) {
                $user = Auth::user();
                $query->where('name', "privcol{$user->id}")
                    ->orWhere('name', 'not like', "privcol%");
            })
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
        $nbPages = ceil(\App\Models\Collection::count() / $pagesSize);

        return view('cywise.iframes.collections', [
            'collections' => $collections,
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'pagesSize' => $pagesSize,
        ]);
    }
}
