<?php

namespace App\Http\Controllers\Iframes;

use App\Http\Controllers\Controller;
use App\Http\Procedures\ChunksProcedure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChunksController extends Controller
{
    public function __invoke(Request $request): View
    {
        $chunks = (new ChunksProcedure())->list($request);
        return view('cywise.iframes.chunks', [
            'chunks' => $chunks['chunks'],
            'collection' => $chunks['collection'],
            'file' => $chunks['file'],
            'nbPages' => $chunks['nb_pages'],
            'currentPage' => $chunks['page'],
            'pagesSize' => $chunks['page_size'],
        ]);
    }
}
