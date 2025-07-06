<?php

namespace App\Console\Commands;

class Answer
{
    private string $answer;
    private bool $success;

    public function __construct(string $answer, bool $success = true)
    {
        $this->answer = $answer;
        $this->success = $success;
    }

    public function __toString()
    {
        return ($this->success ? '[SUCCESS] ' : '[FAILURE]') . $this->answer;
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
}