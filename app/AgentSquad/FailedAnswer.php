<?php

namespace App\AgentSquad;

class FailedAnswer extends Answer
{
    public function __construct(string $answer, array $chainOfThought = [])
    {
        parent::__construct($answer, $chainOfThought, false);
    }
}