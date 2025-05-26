<?php

namespace App\Helpers\Agents;

use App\User;

class AnswerQuestion extends AbstractAction
{
    static function schema(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "answer_question",
                "description" => "Answer the user's question using his own notes.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "answer" => [
                            "type" => ["string"],
                            "description" => "The answer to the user's question'.",
                        ],
                    ],
                    "required" => ["answer"],
                    "additionalProperties" => false,
                ],
                "strict" => true,
            ],
        ];
    }

    public function __construct(User $user, string $threadId, array $args = [])
    {
        parent::__construct($user, $threadId, $args);
    }

    function execute(): AbstractAction
    {
        $this->output = $this->args['answer'] ?? 'Unfortunately an answer could not be found for your question. Please try again later.';
        return $this;
    }
}
