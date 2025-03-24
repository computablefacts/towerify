<?php

namespace App\Modules\TheCyberBrief\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OpenAi
{
    public static function isHyperlink(string $text): bool
    {
        return Str::startsWith(Str::lower($text), ["https://", "http://"]);
    }

    public static function download(string $text): string
    {
        if (self::isHyperlink($text)) {
            if (config('towerify.scrapfly.api_key')) {
                $news = Http::get('https://api.scrapfly.io/scrape?render_js=true&key=' . config('towerify.scrapfly.api_key') . '&url=' . $text);
                return json_decode($news, true)['result']['content'];
            }
            if (config('towerify.scraperapi.api_key')) {
                return Http::get('http://api.scraperapi.com?api_key=' . config('towerify.scraperapi.api_key') . '&url=' . $text);
            }
            Log::error('Missing scraper API key!');
            return '';
        }
        return $text;
    }

    public static function execute(string $prompt, string $model = 'gpt-4o', float $temperature = 0.7, array $tools = []): array
    {
        return self::executeEx([[
            'role' => 'user',
            'content' => $prompt
        ]], $model, $temperature, $tools);
    }

    public static function executeEx(array $messages, string $model = 'gpt-4o', float $temperature = 0.7, array $tools = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('towerify.openai.api_key'),
            'Accept' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'tools' => $tools,
            'tool_choice' => 'auto',
        ]);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }
}
