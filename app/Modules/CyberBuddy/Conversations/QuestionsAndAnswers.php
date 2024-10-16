<?php

namespace App\Modules\CyberBuddy\Conversations;

use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Controllers\CyberBuddyController;
use App\Modules\CyberBuddy\Models\Collection;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Str;

class QuestionsAndAnswers extends Conversation
{
    public ?string $historyKey;
    public ?string $collection;
    public ?string $message;

    public function __construct(string $message)
    {
        $this->historyKey = null;
        $this->collection = null;
        $this->message = $message;
    }

    public function run(): void
    {
        if ($this->historyKey && $this->collection && $this->message) {
            $this->answerQuestion($this->message);
        } else {

            $this->historyKey = Str::uuid()->toString();

            if (Collection::where('is_deleted', false)->count() === 1) {

                $this->collection = Collection::where('is_deleted', false)->first()->name;

                if ($this->message) {
                    $this->answerQuestion($this->message);
                } else {
                    $this->waitTheNextQuestion();
                }
            } else {

                $collections = Collection::where('is_deleted', false)
                    ->get()
                    ->map(fn(Collection $collection) => Button::create($collection->name)->value($collection->id))
                    ->toArray();

                $question = Question::create('Quel corpus de documents souhaitez-vous utiliser?')
                    ->fallback('Le corpus sélectionné est inconnue.')
                    ->callbackId('collection')
                    ->addButtons($collections);

                $this->ask($question, function (Answer $answer) {
                    if ($answer->isInteractiveMessageReply()) {

                        $this->collection = Collection::find($answer->getValue())->name;
                        $this->say("Le corpus selectionné est <b>{$this->collection}</b>.");

                        if ($this->message) {
                            $this->answerQuestion($this->message);
                        } else {
                            $this->waitTheNextQuestion();
                        }
                    }
                });
            }
        }
    }

    private function waitTheNextQuestion(): void
    {
        $this->ask('Que puis-je faire d\'autre pour vous maintenant?', fn(Answer $response) => $this->answerQuestion($response->getText()));
    }

    private function answerQuestion(string $question): void
    {
        $response = ApiUtils::chat_manual_demo($this->historyKey, $this->collection, $question);
        if ($response['error']) {
            $this->say('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
        } else {
            $answer = CyberBuddyController::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
            $this->say($answer);
        }
        $this->waitTheNextQuestion();
    }
}