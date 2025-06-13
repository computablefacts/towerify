<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PromptsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $page = $params['page'] ?? 1;
        $pagesSize = $params['page_size'] ?? 25;
        $prompts = Prompt::query()
            ->where('created_by', Auth::user()->id)
            ->orderBy('name')
            ->forPage($page <= 0 ? 1 : $page, $pagesSize <= 0 ? 25 : $pagesSize)
            ->get();
        $nbPages = ceil(Prompt::count() / $pagesSize);

        return view('cywise.iframes.prompts', [
            'prompts' => $prompts,
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'pagesSize' => $pagesSize,
        ]);
    }
}
