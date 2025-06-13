<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use App\Models\Chunk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChunksController extends Controller
{
    public function __invoke(Request $request): View
    {
        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'collection' => ['nullable', 'string', 'min:1', 'max:100'],
            'file' => ['nullable', 'string', 'min:1', 'max:100'],
        ]);
        $page = $params['page'] ?? 1;
        $pagesSize = $params['page_size'] ?? 25;
        $collection = $params['collection'] ?? null;
        $file = $params['file'] ?? null;
        $query = Chunk::select('cb_chunks.*')
            ->join('cb_collections', 'cb_collections.id', 'cb_chunks.collection_id')
            ->join('cb_files', 'cb_files.id', 'cb_chunks.file_id')
            ->where('cb_chunks.is_deleted', false)
            ->where('cb_collections.is_deleted', false)
            ->where(function ($query) {
                $user = Auth::user();
                $query->where('cb_collections.name', "privcol{$user->id}")
                    ->orWhere('cb_collections.name', 'not like', "privcol%");
            })
            ->orderBy('cb_collections.name')
            ->orderBy('cb_files.name')
            ->orderBy('cb_chunks.page')
            ->orderBy('cb_chunks.id')
            ->forPage($page <= 0 ? 1 : $page, $pagesSize <= 0 ? 25 : $pagesSize);

        if (!empty($collection)) {
            $query->where('cb_collections.name', $collection);
        }
        if (!empty($file)) {
            $query->where('cb_files.name_normalized', $file);
        }

        $chunks = $query->get();

        $query = Chunk::select('cb_chunks.*')
            ->join('cb_collections', 'cb_collections.id', 'cb_chunks.collection_id')
            ->join('cb_files', 'cb_files.id', 'cb_chunks.file_id')
            ->where('cb_chunks.is_deleted', false)
            ->where('cb_collections.is_deleted', false);

        if (!empty($collection)) {
            $query->where('cb_collections.name', $collection);
        }
        if (!empty($file)) {
            $query->where('cb_files.name_normalized', $file);
        }

        $nbPages = ceil($query->count() / $pagesSize);

        return view('cywise.iframes.chunks', [
            'chunks' => $chunks,
            'collection' => $collection,
            'file' => $file,
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'pagesSize' => $pagesSize,
        ]);
    }
}
