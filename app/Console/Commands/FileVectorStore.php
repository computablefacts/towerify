<?php

namespace App\Console\Commands;

class FileVectorStore
{
    private string $directory;
    private int $topK;
    private string $name;
    private string $ext;

    public function __construct(string $directory, int $topK = 4, string $name = 'cywise', string $ext = '.vectors')
    {
        $this->directory = $directory;
        $this->topK = $topK;
        $this->name = $name;
        $this->ext = $ext;

        if (!is_dir($this->directory)) {
            throw new \Exception("Directory '{$this->directory}' does not exist");
        }
    }

    public function delete(): void
    {
        if (file_exists($this->storage())) {
            unlink($this->storage());
        }
    }

    public function addVector(Vector $vector): void
    {
        $this->addVectors([$vector]);
    }

    /** @param Vector[] $vectors */
    public function addVectors(array $vectors): void
    {
        $this->appendToFile($vectors);
    }

    public function deleteVectors(string $key, mixed $value): void
    {
        $tmpFile = $this->directory . DIRECTORY_SEPARATOR . $this->name . '_tmp' . $this->ext;
        $tmpHandle = fopen($tmpFile, 'w');

        if (!$tmpHandle) {
            throw new \Exception("Cannot create temporary file: {$tmpFile}");
        }
        try {
            foreach ($this->line($this->storage()) as $line) {

                $vector = Vector::fromString((string)$line);

                if ($vector->metadata($key) !== $value) {
                    fwrite($tmpHandle, $line);
                }
            }
        } finally {
            fclose($tmpHandle);
        }

        $this->delete();

        if (!rename($tmpFile, $this->storage())) {
            throw new \Exception(self::class . " failed to replace original file.");
        }
    }

    public function filterAndSearch(array $embedding, array $metadata): array
    {
        $topItems = [];

        if (file_exists($this->storage())) {
            foreach ($this->line($this->storage()) as $line) {

                $match = true;
                $vector = Vector::fromString((string)$line);

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

        if (file_exists($this->storage())) {

            foreach ($this->line($this->storage()) as $line) {

                $match = true;
                $vector = Vector::fromString((string)$line);

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
        }
        return $vectors;
    }

    public function search(array $embedding): array
    {
        $topItems = [];

        foreach ($this->line($this->storage()) as $line) {

            $vector = Vector::fromString((string)$line);
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

    /** @param Vector[] $vectors */
    private function appendToFile(array $vectors): void
    {
        file_put_contents(
            $this->storage(),
            implode(PHP_EOL, array_map(fn(Vector $vector) => Vector::toString($vector), $vectors)) . PHP_EOL,
            FILE_APPEND
        );
    }

    private function line(string $filename): \Generator
    {
        $f = fopen($filename, 'r');
        try {
            while ($line = fgets($f)) {
                yield $line;
            }
        } finally {
            fclose($f);
        }
    }

    private function storage(): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->name . $this->ext;
    }
}
