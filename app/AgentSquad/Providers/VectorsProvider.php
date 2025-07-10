<?php

namespace App\AgentSquad\Providers;

use App\AgentSquad\Vectors\AbstractVectorStore;
use App\AgentSquad\Vectors\FileVectorStore;
use Illuminate\Support\Facades\Log;

class VectorsProvider
{
    public static function provide(string $dbName, int $take = 5): ?AbstractVectorStore
    {
        try {
            $start = microtime(true);
            $store = new FileVectorStore(storage_path('app/vectors'), $take, $dbName);
            $stop = microtime(true);
            Log::debug("[VECTORS_PROVIDER] The initialization of the vector database took " . ((int)ceil($stop - $start)) . " seconds");
            return $store;
        } catch (\Exception $e) {
            Log::debug("[VECTORS_PROVIDER] The initialization of the vector database failed");
            Log::error($e->getMessage());
            return null;
        }
    }
}