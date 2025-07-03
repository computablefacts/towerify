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

        $vectors = $this->search($topic);
        $results = array_map(function (array $item): array {
            /** @var Vector $vector */
            $vector = $item['vector'];
            $item['obj'] = $vector->text();
            $item['doc'] = new Document($vector->metadata('file'));
            $item['idx'] = $vector->metadata('index');
            unset($item['vector']);
            return $item;
        }, $vectors);

        $objets = array_unique(array_map(fn(array $item) => $item['obj'], $results));
        $choice = $this->choice('Quel est l\'objet à développer ?', $objets);
        $documents = array_values(array_filter($results, fn(array $item) => $item['obj'] === $choice));
        $nbArguments = count($documents);

        $this->info("{$nbArguments} argumentaire(s) trouvé");

        foreach ($documents as $i => $document) {

            $pos = $i + 1;
            /** @var int $idx */
            $idx = $document['idx'];
            /** @var Document $doc */
            $doc = $document['doc'];
            $this->info("\nL'argumentaire n°{$pos} est composé de {$doc->nbArguments($idx)} arguments :");

            $choices = [];

            for ($j = 0; $j < $doc->nbArguments($idx); $j++) {

                // $this->info("- {$doc->argument($idx, $j)}");

                /** @var string $fait */
                /* foreach ($doc->faits($idx, $j) as $fait) {
                    $this->info("  - {$fait}");
                } */

                $choices[] = $doc->argument($idx, $j);
            }

            $choices[] = 'Passer à l\'argumentaire suivant';
            $choice = $this->choice("Choisissez l'argument à détailler en droit", $choices);

            if ($choice !== 'Passer à l\'argumentaire suivant') {
                $pos = array_search($choice, $choices);
                $enDroit = $doc->en_droit($idx);
                $argument = $doc->argument($idx, $pos);
                $this->info($this->extract($enDroit, $argument));;
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

    private function search(string $text): array
    {
        return $this->vectorStore->search($this->embed($text));
    }

    private function embed(string $text)
    {
        return $this->embeddingProvider->execute($text)['data'][0]['embedding'];
    }
}
