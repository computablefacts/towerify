<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingProvider
{
    const string DEEP_INFRA = 'deep_infra';
    const string OPEN_AI = 'open_ai';

    protected string $provider;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function execute(string $text, ?string $model = null): array
    {
        return match ($this->provider) {
            self::DEEP_INFRA => $this->callDeepInfra($text, $model ?? 'BAAI/bge-m3-multi'),
            self::OPEN_AI => $this->callOpenAi($text, $model ?? 'text-embedding-3-small'),
            default => [],
        };
    }

    private function callDeepInfra(string $text, string $model): array
    {
        return $this->post(config('towerify.deepinfra.api') . '/embeddings', config('towerify.deepinfra.api_key'), $text, $model);
    }

    private function callOpenAi(string $text, string $model): array
    {
        return $this->post(config('towerify.openai.api') . '/embeddings', config('towerify.openai.api_key'), $text, $model);
    }

    private function post(string $url, string $bearer, string $text, string $model): array
    {
        try {

            $payload = [
                'model' => $model,
                'input' => $text,
                'encoding_format' => 'float',
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$bearer}",
                'Accept' => 'application/json',
            ])
                ->timeout(60)
                ->post($url, $payload);

            if ($response->successful()) {
                $json = $response->json();
                // Log::debug($json);
                return $json;
            }
            Log::error($response->body());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return [];
    }
}