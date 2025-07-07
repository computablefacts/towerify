<?php

namespace App\Console\Commands;

use App\AgentSquad\AbstractAction;
use App\AgentSquad\Answer;
use App\Enums\RoleEnum;
use App\Helpers\EmbeddingProvider;
use App\Helpers\LlmProvider;
use App\Models\User;
use Illuminate\Support\Str;

class LabourLawyer extends AbstractAction
{
    private FileVectorStore $vectorStore;
    private EmbeddingProvider $embeddingProvider;
    private LlmProvider $llmProvider;
    private string $llmModel;

    static function schema(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "labour_lawyer",
                "description" => "A lawyer whose specialty is labour law.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "question" => [
                            "type" => ["string"],
                            "description" => "The question to be asked to the lawyer.",
                        ],
                    ],
                    "required" => ["question"],
                    "additionalProperties" => false,
                ],
                "strict" => true,
            ],
        ];
    }

    public function __construct(string $in)
    {
        $this->vectorStore = new FileVectorStore($in);
        $this->embeddingProvider = new EmbeddingProvider(LlmProvider::DEEP_INFRA);
        $this->llmProvider = new LlmProvider(LlmProvider::DEEP_INFRA, 30);
        $this->llmModel = 'Qwen/Qwen3-14B';
    }

    public function execute(User $user, string $threadId, array $messages, string $input): Answer
    {
        $results = array_merge(array_map(function (array $item): array {

            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');
            $item['idx_arg'] = $vector->metadata('index_argument');

            unset($item['vector']);
            return $item;
        }, $this->searchArguments($input)), array_map(function (array $item): array {

            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');

            unset($item['vector']);
            return $item;
        }, $this->searchObjets($input)));

        $enDroit = implode("\n", array_unique(array_map(fn(array $item) => "[ARGUMENT][OBJ]{$item['obj']}[/OBJ][ARG]{$item['doc']->en_droit($item['idx'])}[/ARG][/ARGUMENT]", $results)));

        $prompt = "
Tu es un avocat en droit social.
Tu cherches à développer un argumentaire juridique succinct lié à : {$input}
Tu n'utiliseras pas de markdown pour formuler ta réponse.
Utilise le texte contenu entre les balises [EN_DROIT] et [/EN_DROIT] pour développer cet argumentaire.
N'hésite pas à extraire du texte contenu entre les balises [EN_DROIT] et [/EN_DROIT] des citations de la loi pour appuyer ton argumentaire.

[EN_DROIT]
{$enDroit}
[/EN_DROIT]
        ";

        $messages[] = [
            'role' => RoleEnum::USER->value,
            'content' => $prompt,
        ];
        $response = $this->llmProvider->execute($messages, $this->llmModel);
        array_pop($messages);
        $answer = $response['choices'][0]['message']['content'] ?? '';
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
        return new Answer(Str::trim($answer));
    }

    private function searchObjets(string $text): array
    {
        return $this->vectorStore->search($this->embed($text));
    }

    private function searchArguments(string $text): array
    {
        return $this->vectorStore->filterAndSearch($this->embed($text), [
            'type' => 'argument',
        ]);
    }

    private function embed(string $text)
    {
        return $this->embeddingProvider->execute($text)['data'][0]['embedding'];
    }
}