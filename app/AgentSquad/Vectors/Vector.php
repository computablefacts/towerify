<?php

namespace App\AgentSquad\Vectors;

use JsonSerializable;

class Vector implements JsonSerializable
{
    private string $text;
    private array $embedding;
    private array $metadata;

    public static function toString(Vector $vector): string
    {
        return json_encode($vector->jsonSerialize());
    }

    public static function fromString(string $str): Vector
    {
        $array = json_decode($str, true);
        return new self($array['text'], $array['embedding'], $array['metadata']);
    }

    public function __construct(string $text, array $embedding = [], array $metadata = [])
    {
        $this->text = $text;
        $this->embedding = $embedding;
        $this->metadata = $metadata;
    }

    public function jsonSerialize()
    {
        return [
            'text' => $this->text,
            'embedding' => $this->embedding,
            'metadata' => $this->metadata,
        ];
    }

    public function text(): string
    {
        return $this->text;
    }

    public function embedding(): array
    {
        return $this->embedding;
    }

    public function metadata(string $key): mixed
    {
        return $this->metadata[$key] ?? null;
    }
}
