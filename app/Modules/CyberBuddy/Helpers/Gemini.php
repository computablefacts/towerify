<?php

namespace App\Modules\CyberBuddy\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Gemini
{
    private function __construct()
    {
        //
    }

    public static function execute(string $prompt, string $model = 'gemini-2.0-flash', float $temperature = 0.7, array $tools = [])
    {
        return self::executeEx([[
            'role' => 'user',
            'content' => $prompt
        ]], $model, $temperature, $tools);
    }

    public static function executeEx(array $messages, string $model = 'gemini-2.0-flash', float $temperature = 0.7, array $tools = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('towerify.gemini.api_key'),
            'Accept' => 'application/json',
        ])
            ->timeout(60)
            ->post(config('towerify.gemini.api') . '/chat/completions', [
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