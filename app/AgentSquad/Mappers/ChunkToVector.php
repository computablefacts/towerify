<?php

namespace App\AgentSquad\Mappers;

use App\AgentSquad\Providers\EmbeddingsProvider;
use App\AgentSquad\Vectors\Vector;
use App\Models\Chunk;

class ChunkToVector
{
    public static function map(Chunk $chunk): Vector
    {
        $tenant = $chunk->tenant();
        return EmbeddingsProvider::provide($chunk->text, [
            'id' => $chunk->id,
            'tenant_id' => $tenant?->id ?? 0,
            'user_id' => $chunk->created_by,
            'collection_id' => $chunk->collection_id,
            'file_id' => $chunk->file_id,
        ]);
    }
}