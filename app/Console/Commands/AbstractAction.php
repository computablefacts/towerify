<?php

namespace App\Console\Commands;

use App\Models\User;

abstract class AbstractAction
{
    abstract static function schema(): array;

    public function name(): string
    {
        return $this->schema()['function']['name'] ?? '';
    }

    public function description(): string
    {
        return $this->schema()['function']['description'] ?? '';
    }

    public abstract function execute(User $user, string $threadId, array $messages, string $input): Answer;
}