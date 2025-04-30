<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepInfra
{
    private function __construct()
    {
        //
    }

    public static function execute(string $prompt, string $model = 'meta-llama/Meta-Llama-3-70B-Instruct', float $temperature = 0.7, array $tools = [])
    {
        return self::executeEx([[
            'role' => 'user',
            'content' => $prompt
        ]], $model, $temperature, $tools);
    }

    public static function executeEx(array $messages, string $model = 'meta-llama/Meta-Llama-3-70B-Instruct', float $temperature = 0.7, array $tools = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('towerify.deepinfra.api_key'),
            'Accept' => 'application/json',
        ])
            ->timeout(120)
            ->post(config('towerify.deepinfra.api') . '/chat/completions', [
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