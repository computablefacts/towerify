<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'collection' => ['nullable', 'string', 'min:1', 'max:100'],
        ]);
        $page = $params['page'] ?? 1;
        $pagesSize = $params['page_size'] ?? 25;
        $collection = $params['collection'] ?? null;
        $query = File::select('cb_files.*')
            ->join('cb_collections', 'cb_collections.id', '=', 'cb_files.collection_id')
            ->where('cb_files.is_deleted', false)
            ->where('cb_collections.is_deleted', false)
            ->where(function ($query) {
                $user = Auth::user();
                $query->where('cb_collections.name', "privcol{$user->id}")
                    ->orWhere('cb_collections.name', 'not like', "privcol%");
            })
            ->orderBy('cb_collections.name')
            ->orderBy('name_normalized')
            ->forPage($page <= 0 ? 1 : $page, $pagesSize <= 0 ? 25 : $pagesSize);

        if (!empty($collection)) {
            $query->where('cb_collections.name', $collection);
        }

        $files = $query->get()
            ->map(function (File $file) {
                $nbChunks = $file->chunks()->count();
                $nbVectors = $file->chunks()->where('is_embedded', true)->count();
                $nbNotVectors = $file->chunks()->where('is_embedded', false)->count();
                return [
                    'id' => $file->id,
                    'name_normalized' => $file->name_normalized,
                    'collection' => $file->collection->name,
                    'filename' => "{$file->name_normalized}.{$file->extension}",
                    'created_at' => $file->created_at,
                    'created_by' => $file->createdBy(),
                    'size' => $file->size,
                    'nb_chunks' => $nbChunks,
                    'nb_vectors' => $nbVectors,
                    'nb_not_vectors' => $nbNotVectors,
                    'status' => $file->is_embedded ? 'processed' : 'processing',
                    'download_url' => $file->downloadUrl(),
                ];
            });
        $nbPages = ceil(File::select('cb_files.*')
                ->join('cb_collections', 'cb_collections.id', '=', 'cb_files.collection_id')
                ->where('cb_files.is_deleted', false)
                ->where('cb_collections.is_deleted', false)->count() / $pagesSize);

        return view('cywise.iframes.documents', [
            'files' => $files,
            'collection' => $collection,
            'nbPages' => $nbPages,
            'currentPage' => $page,
            'pagesSize' => $pagesSize,
        ]);
    }
}
