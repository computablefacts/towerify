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

    protected function storage(): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->name . $this->ext;
    }

    public function addDocument(array $document): void
    {
        $this->addDocuments([$document]);
    }

    public function addDocuments(array $documents): void
    {
        $this->appendToFile($documents);
    }

    public function search(array $embedding): array
    {
        $topItems = [];

        foreach ($this->line($this->storage()) as $document) {

            $document = json_decode((string)$document, true);

            if (empty($document['embedding'])) {
                throw new \Exception("Document with the following content has no embedding: {$document['text']}");
            }

            $dist = VectorsSimilarity::cosineDistance($embedding, $document['embedding']);

            unset($document['embedding']);

            $topItems[] = [
                'dist' => $dist,
                'document' => $document,
            ];

            usort($topItems, fn(array $a, array $b): int => $a['dist'] <=> $b['dist']);

            if (count($topItems) > $this->topK) {
                $topItems = array_slice($topItems, 0, $this->topK, true);
            }
        }
        return array_map(function (array $item): array {
            $item['similarity'] = VectorsSimilarity::similarityFromDistance($item['dist']);
            unset($item['dist']);
            return $item;
        }, $topItems);
    }

    public function delete(string $file, int $index): void
    {
        // Temporary file
        $tmpFile = $this->directory . DIRECTORY_SEPARATOR . $this->name . '_tmp' . $this->ext;

        // Create a temporary file handle
        $tempHandle = fopen($tmpFile, 'w');

        if (!$tempHandle) {
            throw new \Exception("Cannot create temporary file: {$tmpFile}");
        }
        try {
            foreach ($this->line($this->storage()) as $line) {
                $document = json_decode((string)$line, true);
                if ($document['file'] !== $file || $document['index'] !== $index) {
                    fwrite($tempHandle, $line);
                }
            }
        } finally {
            fclose($tempHandle);
        }

        // Replace the original file with the filtered version
        \unlink($this->storage());

        if (!\rename($tmpFile, $this->storage())) {
            throw new \Exception(self::class . " failed to replace original file.");
        }
    }

    private function appendToFile(array $documents): void
    {
        file_put_contents(
            $this->storage(),
            implode(PHP_EOL, array_map(fn(array $vector) => json_encode($vector), $documents)) . PHP_EOL,
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
}
