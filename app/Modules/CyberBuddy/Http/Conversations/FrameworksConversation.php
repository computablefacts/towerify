<?php

namespace App\Modules\CyberBuddy\Http\Conversations;

use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Controllers\CyberBuddyController;
use App\Modules\CyberBuddy\Models\Collection;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Str;

class FrameworksConversation extends Conversation
{
    public function run(): void
    {
        $this->askForFramework();
    }

    public function stopsConversation(IncomingMessage $message): bool
    {
        return $message->getText() === 'stop';
    }

    private function askForFramework(): void
    {
        $collections = Collection::all()
            ->map(fn(Collection $collection) => Button::create($collection->name)->value($collection->id))
            ->toArray();
        $question = Question::create('Quelle version souhaitez-vous utiliser?')
            ->fallback('La version sélectionnée est inconnue.')
            ->callbackId('guide_hygiene_anssi')
            ->addButtons($collections);
        $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $collectionId = $answer->getValue();
                $collection = Collection::find($collectionId);
                $this->say("Le référentiel est maintenant <b>{$collection->name}</b>.");
                $this->askQuestion(Str::random(), $collection);
            }
        });
    }

    private function askQuestion(string $historyKey, Collection $collection): void
    {
        $this->ask('Posez-moi une question!', function (Answer $answer) use ($historyKey, $collection) {
            $response = ApiUtils::chat_manual_demo($historyKey, $collection->name, $answer->getText());
            if ($response['error']) {
                $this->say('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
            } else {
                $answer = CyberBuddyController::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
                $this->say($answer);
                $this->askAnotherQuestion($historyKey, $collection);
            }
        });
    }

    private function askAnotherQuestion(string $historyKey, Collection $collection): void
    {
        $this->ask('Une autre question?', function (Answer $answer) use ($historyKey, $collection) {
            $response = ApiUtils::chat_manual_demo($historyKey, $collection->name, $answer->getText());
            if ($response['error']) {
                $this->say('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
            } else {
                $answer = CyberBuddyController::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
                $this->say($answer);
                $this->askAnotherQuestion($historyKey, $collection);
            }
        });
    }
}
