<?php

namespace App\AgentSquad\Answers;

class SuccessfulAnswer extends AbstractAnswer
{
    public function __construct(string $answer, array $chainOfThought = [])
    {
        parent::__construct($answer, $chainOfThought, true);
    }
}