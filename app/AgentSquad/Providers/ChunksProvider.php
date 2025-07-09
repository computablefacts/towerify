<?php

namespace App\AgentSquad\Providers;

use App\Models\Chunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChunksProvider
{
    public static function provide(Collection $collections, string $language, string $keywords, int $take = 50): Collection
    {
        try {
            $start = microtime(true);
            $chunks = Chunk::search("{$language}:{$keywords}")
                ->whereIn('collection_id', $collections->pluck('id'))
                ->take($take)
                ->get();
            $stop = microtime(true);
            Log::debug("[CHUNKS_PROVIDER] Search for '{$keywords}:{$keywords}' took " . ((int)ceil($stop - $start)) . " seconds and returned {$chunks->count()} results");
            return $chunks;
        } catch (\Exception $e) {
            Log::debug("[CHUNKS_PROVIDER] Search for '{$keywords}:{$keywords}' failed");
            Log::error($e->getMessage());
            return collect();
        }
    }
}