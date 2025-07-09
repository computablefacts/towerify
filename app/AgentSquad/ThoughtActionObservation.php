<?php

namespace App\AgentSquad;

use JsonSerializable;

class ThoughtActionObservation implements JsonSerializable
{
    private string $thought;
    private string $action;
    private string $observation;

    public function __construct(string $thought, string $action, string $observation)
    {
        $this->thought = $thought;
        $this->action = $action;
        $this->observation = $observation;
    }

    public function jsonSerialize(): array
    {
        return [
            'thought' => $this->thought,
            'action' => $this->action,
            'observation' => $this->observation,
        ];
    }

    public function thought(): string
    {
        return $this->thought;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function observation(): string
    {
        return $this->observation;
    }
}