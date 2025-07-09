<?php

namespace App\AgentSquad\Answers;

use App\AgentSquad\ThoughtActionObservation;

abstract class AbstractAnswer
{
    private string $answer;
    /** @var ThoughtActionObservation[] $chainOfThought */
    private array $chainOfThought;
    private bool $success;

    protected function __construct(string $answer, array $chainOfThought = [], bool $success = true)
    {
        $this->answer = $answer;
        $this->chainOfThought = $chainOfThought;
        $this->success = $success;
    }

    public function __toString()
    {
        return ($this->success ? '[SUCCESS] ' : '[FAILURE] ') . $this->answer;
    }

    public function chainOfThought(): array
    {
        return $this->chainOfThought;
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function failure(): bool
    {
        return !$this->success;
    }

    public function html(): string
    {
        return $this->answer;
    }

    public function markdown(): string
    {
        return $this->answer;
    }

    /** @param ThoughtActionObservation[] $chainOfThought */
    public function setChainOfThought(array $chainOfThought = []): void
    {
        $this->chainOfThought = $chainOfThought;
    }
}