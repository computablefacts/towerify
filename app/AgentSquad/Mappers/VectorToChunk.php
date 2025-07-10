<?php

namespace App\AgentSquad\Mappers;

use App\AgentSquad\Vectors\Vector;
use App\Models\Chunk;

class VectorToChunk
{
    public static function map(Vector $vector): Chunk
    {
        return Chunk::find($vector->metadata('id'));
    }
}