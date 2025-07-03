<?php

namespace App\Console\Commands;

use App\Helpers\EmbeddingProvider;
use App\Helpers\LlmProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PrepareLegalDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legal:prepare {input} {output} {prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert a legal document to a list of chunks.';

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
        $out = $this->argument('output');
        $prompt = $this->argument('prompt');
        $this->vectorStore = new FileVectorStore($out);
        $this->embeddingProvider = new EmbeddingProvider(LlmProvider::DEEP_INFRA);
        $this->llmProvider = new LlmProvider(LlmProvider::DEEP_INFRA, 30 * 60);
        $this->llmModel = 'google/gemini-2.5-pro';

        if (is_dir($in)) {
            //$this->processDirectory($in, $out, $prompt);
        } elseif (is_file($in)) {
            $this->processFile($in, $in, $prompt);
        } else {
            throw new \Exception('Invalid input path : ' . $in);
        }

        $this->updateVectorDatabase($out);
    }

    private function processDirectory(string $dir, string $output, string $prompt): void
    {
        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        if (count($ffs) < 1) {
            return;
        }
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff)) {
                $this->processDirectory($dir . '/' . $ff, $output, $prompt);
            } else if (is_file($dir . '/' . $ff)) {
                $this->processFile($dir . '/' . $ff, $output, $prompt);
            }
        }
    }

    private function processFile(string $file, string $output, string $prompt): void
    {
        if (!Str::endsWith($file, '.docx') && !Str::endsWith($file, '.doc')) {
            Log::warning("Skipping file $file : not a .docx file.");
            return;
        }

        $filename = Str::slug(basename($file));
        $md = "{$output}/{$filename}.md";
        $json = "{$output}/{$filename}.json";

        if (!file_exists($md)) {
            shell_exec("pandoc -t markdown_strict --extract-media=\"{$output}/attachments/{$filename}\" \"$file\" -o \"$md\"");
        }
        if (!file_exists($md)) {
            Log::warning("Skipping file $file : no markdown file.");
            return;
        }
        if (!file_exists($json)) {

            $markdown = file_get_contents($md);
            $prompt = \Illuminate\Support\Facades\File::get($prompt);
            $prompt = Str::replace('{DOC}', $markdown, $prompt);
            $response = $this->llmProvider->execute($prompt, $this->llmModel);
            $answer = $response['choices'][0]['message']['content'] ?? '';
            $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
            $array = json_decode($answer, true);

            if ($array) {
                file_put_contents($json, json_encode($array, JSON_PRETTY_PRINT));
            }
        }
        if (!file_exists($json)) {
            Log::warning("Skipping file $file : no json file.");
            return;
        }
    }

    private function updateVectorDatabase(string $output): void
    {
        // $this->vectorStore->delete();
        $files = glob("{$output}/*.json");

        /** @var string $file */
        foreach ($files as $file) {

            $document = new Document($file);

            for ($i = 0; $i < $document->nbObjets(); $i++) {

                $metadata = [
                    'file' => $file,
                    'index' => $i,
                ];

                if (empty($this->vectorStore->find($metadata))) {
                    $topic = $this->topic($document->objet($i));
                    $vector = new Vector($topic, $this->embed($topic), $metadata);
                    $this->vectorStore->addVector($vector);
                }
            }
        }
    }

    private function topic(string $str): string
    {
        $prompt = "
            Ta réponse devra être en plein texte sans markdown.
            Retourne uniquement l'objet de cette phrase en enlevant notamment les noms de personnes et de sociétés :
            
            {$str}
        ";
        $response = $this->llmProvider->execute($prompt, $this->llmModel);
        $answer = $response['choices'][0]['message']['content'] ?? '';
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
        return trim($answer);
    }

    private function embed(string $text)
    {
        return $this->embeddingProvider->execute($text)['data'][0]['embedding'];
    }
}
