<?php

namespace App\Console\Commands;

use App\AgentSquad\AbstractAction;
use App\AgentSquad\Answers\AbstractAnswer;
use App\AgentSquad\Answers\SuccessfulAnswer;
use App\AgentSquad\Providers\EmbeddingsProvider;
use App\AgentSquad\Providers\LlmsProvider;
use App\AgentSquad\Vectors\AbstractVectorStore;
use App\AgentSquad\Vectors\FileVectorStore;
use App\AgentSquad\Vectors\Vector;
use App\Enums\RoleEnum;
use App\Models\User;

class LabourLawyer extends AbstractAction
{
    private AbstractVectorStore $vectorStore;
    private string $model;

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
        $this->model = 'Qwen/Qwen3-14B';
    }

    public function execute(User $user, string $threadId, array $messages, string $input): AbstractAnswer
    {
        $vector = EmbeddingsProvider::provide($input);
        $objets = $this->vectorStore->search($vector->embedding());
        $arguments = $this->vectorStore->filterAndSearch($vector->embedding(), ['type' => 'argument']);
        $results = array_merge(array_map(function (array $item): array {

            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');
            $item['idx_arg'] = $vector->metadata('index_argument');

            unset($item['vector']);
            return $item;
        }, $arguments), array_map(function (array $item): array {

            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');

            unset($item['vector']);
            return $item;
        }, $objets));

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
        $answer = LlmsProvider::provide($messages, $this->model, 30);
        array_pop($messages);
        return new SuccessfulAnswer($answer);
    }
}