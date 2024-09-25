<?php

namespace App\Modules\TheCyberBrief\Http\Controllers;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Modules\TheCyberBrief\Models\YnhTheCyberBrief;
use Illuminate\Http\Request;

class TheCyberBriefController extends Controller
{
    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        $compact = $request->boolean('compact', true);
        $language = $request->string('lang', 'en');

        if ($language == 'fr') {
            $lang = LanguageEnum::FRENCH;
        } else {
            $lang = LanguageEnum::ENGLISH;
        }
        
        $briefes = YnhTheCyberBrief::where('is_published', true)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('modules.the-cyber-brief.index', compact('lang', 'compact', 'briefes'));
    }
}
