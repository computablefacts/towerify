<?php

namespace App\AgentSquad\Vectors;

abstract class AbstractVectorStore
{
    private int $topK;

    public function __construct(int $topK = 4)
    {
        $this->topK = $topK;
    }

    public function addVector(Vector $vector): void
    {
        $this->addVectors([$vector]);
    }

    public function filterAndSearch(array $embedding, array $metadata): array
    {
        $topItems = [];

        /** @var Vector $vector */
        foreach ($this->vectors() as $vector) {

            $match = true;

            foreach ($metadata as $key => $value) {
                if ($vector->metadata($key) !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {

                $vectorEmbedding = $vector->embedding();

                if (empty($vectorEmbedding)) {
                    throw new \Exception("Vector with the following content has no embedding: {$vector->text()}");
                }

                $dist = VectorsSimilarity::cosineDistance($embedding, $vectorEmbedding);
                $topItems[] = [
                    'distance' => $dist,
                    'vector' => $vector,
                ];

                usort($topItems, fn(array $a, array $b): int => $a['distance'] <=> $b['distance']);

                if (count($topItems) > $this->topK) {
                    $topItems = array_slice($topItems, 0, $this->topK, true);
                }
            }
        }
        return array_map(function (array $item): array {
            $item['similarity'] = VectorsSimilarity::similarityFromDistance($item['distance']);
            unset($item['distance']);
            return $item;
        }, $topItems);
    }

    /** @return Vector[] */
    public function find(array $metadata): array
    {
        $vectors = [];

        /** @var Vector $vector */
        foreach ($this->vectors() as $vector) {

            $match = true;

            foreach ($metadata as $key => $value) {
                if ($vector->metadata($key) !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $vectors[] = $vector;
            }
        }
        return $vectors;
    }

    public function search(array $embedding): array
    {
        $topItems = [];

        /** @var Vector $vector */
        foreach ($this->vectors() as $vector) {

            $vectorEmbedding = $vector->embedding();

            if (empty($vectorEmbedding)) {
                throw new \Exception("Vector with the following content has no embedding: {$vector->text()}");
            }

            $dist = VectorsSimilarity::cosineDistance($embedding, $vectorEmbedding);
            $topItems[] = [
                'distance' => $dist,
                'vector' => $vector,
            ];

            usort($topItems, fn(array $a, array $b): int => $a['distance'] <=> $b['distance']);

            if (count($topItems) > $this->topK) {
                $topItems = array_slice($topItems, 0, $this->topK, true);
            }
        }
        return array_map(function (array $item): array {
            $item['similarity'] = VectorsSimilarity::similarityFromDistance($item['distance']);
            unset($item['distance']);
            return $item;
        }, $topItems);
    }

    public abstract function clear(): void;

    /** @param Vector[] $vectors */
    public abstract function addVectors(array $vectors): void;

    protected abstract function vectors(): \Generator;
}
