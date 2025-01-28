<?php

namespace App\Modules\TheCyberBrief\Http\Controllers;

use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Modules\TheCyberBrief\Helpers\OpenAi;
use App\Modules\TheCyberBrief\Models\Stories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function summarize(Request $request)
    {
        $request->validate([
            'url_or_text' => 'required|string',
            'prompt' => 'required|string',
        ]);
        $text = $request->string('url_or_text', '');
        $prompt = $request->string('prompt', '');
        $model = $request->string('model', 'gpt-4o');
        $temperature = $request->float('temperature', 0.7);
        $content = OpenAi::download($text);
        $response = OpenAi::summarize(Str::replace('[TEXT]', $content, $prompt), $model, $temperature);
        return isset($response['choices'][0]['message']['content']) ?
            response()->json(['status' => 'ok', 'summary' => $response['choices'][0]['message']['content']]) :
            response()->json(['status' => 'ko', 'summary' => null], 500);
    }
}
