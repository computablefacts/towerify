<?php

namespace App\Console\Commands;

use App\Helpers\EmbeddingProvider;
use App\Helpers\LlmProvider;
use Illuminate\Console\Command;

class FindLegalArguments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legal:find {input}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find legal arguments.';

    private FileVectorStore $vectorStore;
    private EmbeddingProvider $embeddingProvider;
    private LlmProvider $llmProvider;
    private string $llmModel;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $in = $this->argument('input');
        $this->vectorStore = new FileVectorStore($in);
        $this->embeddingProvider = new EmbeddingProvider(LlmProvider::DEEP_INFRA);
        $this->llmProvider = new LlmProvider(LlmProvider::DEEP_INFRA, 30);
        $this->llmModel = 'Qwen/Qwen3-14B';

        $topic = $this->ask('Quelle est la thématique à développer ?');

        $results = array_merge(array_map(function (array $item): array {

            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');
            $item['idx_arg'] = $vector->metadata('index_argument');

            unset($item['vector']);
            return $item;
        }, $this->searchArguments($topic)), array_map(function (array $item): array {

            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');

            unset($item['vector']);
            return $item;
        }, $this->searchObjets($topic)));

        $objets = array_unique(array_map(fn(array $item) => $item['obj'], $results));
        $choice = $this->choice('Quel est l\'objet à développer ?', $objets);
        $items = array_values(array_filter($results, fn(array $item) => $item['obj'] === $choice));

        foreach ($items as $item) {
            if (isset($item['idx_arg'])) {

                /** @var int $idxObj */
                $idxObj = $item['idx'];
                /** @var int $idxArg */
                $idxArg = $item['idx_arg'];
                /** @var Document $doc */
                $doc = $item['doc'];

                $enDroit = $doc->en_droit($idxObj);
                $argument = $doc->argument($idxObj, $idxArg);
                $this->info($this->extract($enDroit, $argument));
            } else {

                /** @var int $idxObj */
                $idxObj = $item['idx'];
                /** @var Document $doc */
                $doc = $item['doc'];

                $objets = [];

                for ($idxArg = 0; $idxArg < $doc->nbArguments($idxObj); $idxArg++) {
                    $objets[] = $doc->argument($idxObj, $idxArg);
                }

                $objets[] = "Passer à l'objet suivant";
                $choice = $this->choice('Quel est l\'objet à développer ?', $objets);

                if ($choice !== "Passer à l'objet suivant") {
                    $idxArg = array_search($choice, $objets);
                    $enDroit = $doc->en_droit($idxObj);
                    $argument = $doc->argument($idxObj, $idxArg);
                    $this->info($this->extract($enDroit, $argument));
                }
            }
        }
    }

    private function extract(string $enDroit, string $argument): string
    {
        $prompt = "
            Tu es un avocat en droit social.
            Tu cherches à développer un argumentaire juridique succinct lié à : {$argument}
            Tu n'utiliseras pas de markdown pour formuler ta réponse.
            Utilise le texte contenu entre les balises [EN_DROIT] et [/EN_DROIT] pour développer cet argumentaire.
            N'hésite pas à extraire du texte contenu entre les balises [EN_DROIT] et [/EN_DROIT] des citations de la loi pour appuyer ton argumentaire.
            
            [EN_DROIT]
            {$enDroit}
            [/EN_DROIT]
        ";
        $response = $this->llmProvider->execute($prompt, $this->llmModel);
        $answer = $response['choices'][0]['message']['content'] ?? '';
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
        return trim($answer);
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
