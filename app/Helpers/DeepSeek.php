<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeek
{
    private function __construct()
    {
        //
    }

    public static function execute(string $prompt, string $model = 'deepseek-chat', float $temperature = 0.7, array $tools = []): array
    {
        return self::executeEx([[
            'role' => 'user',
            'content' => $prompt
        ]], $model, $temperature, $tools);
    }

    public static function executeEx(array $messages, string $model = 'deepseek-chat', float $temperature = 0.7, array $tools = []): array
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'stream' => false,
        ];
        if (count($tools) > 0) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('towerify.deepseek.api_key'),
            'Accept' => 'application/json',
        ])
            ->timeout(120)
            ->post(config('towerify.deepseek.api') . '/chat/completions', $payload);
        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json;
        }
        Log::error($response->body());
        return [];
    }
}