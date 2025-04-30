<?php

namespace App\View\Components;

use App\Models\Chunk;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Chunks extends Component
{
    public Collection $chunks;
    public string $collection;
    public string $file;
    public int $nbPages;
    public int $pagesSize;
    public int $currentPage;

    public function __construct(int $currentPage, int $pagesSize = 25, ?string $collection = null, ?string $file = null)
    {
        $query = Chunk::select('cb_chunks.*')
            ->join('cb_collections', 'cb_collections.id', 'cb_chunks.collection_id')
            ->join('cb_files', 'cb_files.id', 'cb_chunks.file_id')
            ->where('cb_chunks.is_deleted', false)
            ->where('cb_collections.is_deleted', false)
            ->orderBy('cb_collections.name')
            ->orderBy('cb_files.name')
            ->orderBy('cb_chunks.page')
            ->orderBy('cb_chunks.id')
            ->forPage($currentPage <= 0 ? 1 : $currentPage, $pagesSize <= 0 ? 25 : $pagesSize);

        if (!empty($collection)) {
            $query->where('cb_collections.name', $collection);
        }
        if (!empty($file)) {
            $query->where('cb_files.name_normalized', $file);
        }

        $this->chunks = $query->get();

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

        $this->collection = empty($collection) ? '' : $collection;
        $this->file = empty($file) ? '' : $file;
        $this->nbPages = ceil($query->count() / $pagesSize);
        $this->currentPage = $currentPage;
        $this->pagesSize = $pagesSize;
    }

    public function render(): View|Closure|string
    {
        return view('components.chunks');
    }
}
