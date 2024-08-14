<?php

namespace App\Http\Controllers;

use App\Models\YnhTheCyberBrief;

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

        return view('the-cyber-brief.index', compact('briefes'));
    }
}
