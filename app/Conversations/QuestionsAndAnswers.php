<?php

namespace App\Conversations;

use App\Helpers\ApiUtilsFacade as ApiUtils;
use App\Http\Controllers\CyberBuddyController;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Support\Str;

class QuestionsAndAnswers extends Conversation
{
    public ?string $historyKey;
    public ?string $message;

    public function __construct(string $message)
    {
        $this->historyKey = null;
        $this->message = $message;
    }

    public function run(): void
    {
        if ($this->historyKey && $this->message) {
            $this->answerQuestion($this->message);
        } else {
            $this->historyKey = Str::uuid()->toString();
            if ($this->message) {
                $this->answerQuestion($this->message);
            } else {
                $this->waitTheNextQuestion();
            }
        }
    }

    private function waitTheNextQuestion(): void
    {
        // We neither want to stop the conversation nor direct it too much :
        // return an empty answer that won't be displayed by the chat widget
        $this->ask('', fn(Answer $response) => $this->answerQuestion($response->getText()));
    }

    private function answerQuestion(string $question): void
    {
        $question = htmlspecialchars($question, ENT_QUOTES, 'UTF-8');
        $response = ApiUtils::chat_manual_demo($this->historyKey, null, $question);
        if ($response['error']) {
            $this->say('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
        } else {
            $answer = CyberBuddyController::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
            $this->say($answer);
        }
        $this->waitTheNextQuestion();
    }
}