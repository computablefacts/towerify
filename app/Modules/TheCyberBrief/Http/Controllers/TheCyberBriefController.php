<?php

namespace App\Modules\TheCyberBrief\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\TheCyberBrief\Models\YnhTheCyberBrief;

class TheCyberBriefController extends Controller
{
    public function __construct()
    {
        //
    }

    public function index()
    {
        $briefes = YnhTheCyberBrief::where('is_published', true)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('modules.the-cyber-brief.index', compact('briefes'));
    }
}
