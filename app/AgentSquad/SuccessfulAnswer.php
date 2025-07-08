<?php

namespace App\AgentSquad;

class SuccessfulAnswer extends Answer
{
    public function __construct(string $answer, array $chainOfThought = [])
    {
        parent::__construct($answer, $chainOfThought, true);
    }
}