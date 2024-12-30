<?php

namespace App\View\Components;

use App\Modules\CyberBuddy\Models\File;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Documents extends Component
{
    public Collection $files;
    public string $collection;
    public int $nbPages;
    public int $pagesSize;
    public int $currentPage;

    public function __construct(int $currentPage, int $pagesSize = 25, ?string $collection = null)
    {
        $query = File::select('cb_files.*')
            ->join('cb_collections', 'cb_collections.id', '=', 'cb_files.collection_id')
            ->where('cb_files.is_deleted', false)
            ->where('cb_collections.is_deleted', false)
            ->orderBy('cb_collections.name')
            ->orderBy('name_normalized')
            ->forPage($currentPage <= 0 ? 1 : $currentPage, $pagesSize <= 0 ? 25 : $pagesSize);

        if (!empty($collection)) {
            $query->where('cb_collections.name', $collection);
        }

        $this->files = $query->get()
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
        $this->collection = empty($collection) ? '' : $collection;
        $this->nbPages = ceil(File::select('cb_files.*')
                ->join('cb_collections', 'cb_collections.id', '=', 'cb_files.collection_id')
                ->where('cb_files.is_deleted', false)
                ->where('cb_collections.is_deleted', false)->count() / $pagesSize);
        $this->currentPage = $currentPage;
        $this->pagesSize = $pagesSize;
    }

    public function render(): View|Closure|string
    {
        return view('components.documents');
    }
}
