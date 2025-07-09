<?php

namespace App\AgentSquad\Vectors;

use Illuminate\Support\Facades\DB;

class DatabaseVectorStore extends AbstractVectorStore
{
    public function __construct(int $topK = 4)
    {
        parent::__construct($topK);
    }

    public function clear(): void
    {
        DB::table('cb_vectors')->truncate();
    }

    /** @param Vector[] $vectors */
    public function addVectors(array $vectors): void
    {
        DB::transaction(function () use ($vectors) {
            foreach ($vectors as $vector) {
                DB::table('cb_vectors')->insert($vector->toArray());
            }
        });
    }

    protected function vectors(): \Generator
    {
        $vectors = DB::table('cb_vectors')->cursor();
        foreach ($vectors as $vector) {
            yield new Vector(
                $vector->text,
                json_decode($vector->embedding, true),
                json_decode($vector->metadata, true)
            );
        }
    }
}
