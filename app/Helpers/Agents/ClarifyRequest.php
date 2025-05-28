<?php

namespace App\Helpers\Agents;

use App\User;

class ClarifyRequest extends AbstractAction
{
    private string $message;

    static function schema(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "clarify_request",
                "description" => "Ask user for clarification when request is unclear.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "question" => [
                            "type" => ["string"],
                            "description" => "Question to ask user for clarification.",
                        ],
                    ],
                    "required" => ["question"],
                    "additionalProperties" => false,
                ],
                "strict" => true,
            ],
        ];
    }

    public function __construct(User $user, string $threadId, array $messages, array $args = [], string $message = "I'm sorry, but I didn't understand your request. Could you please provide more details or rephrase your question?")
    {
        parent::__construct($user, $threadId, $messages, $args);
        $this->message = $message;
    }

    function execute(): AbstractAction
    {
        $this->output = $this->args['question'] ?? $this->message;
        return $this;
    }
}
