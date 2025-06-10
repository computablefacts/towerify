<?php

namespace App\Http\Controllers;

use App\Enums\LanguageEnum;
use App\Models\Stories;
use Illuminate\Http\Request;

/** @deprecated */
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

        $briefes = Stories::where('is_published', true)
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        return view('modules.the-cyber-brief.index', compact('lang', 'compact', 'briefes'));
    }
}
