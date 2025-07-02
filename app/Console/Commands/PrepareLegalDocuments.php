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

    private FileVectorStore $vectors;
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
        $this->vectors = new FileVectorStore($out, 4, 'objets', '.vectors');
        $this->embeddingProvider = new EmbeddingProvider(LlmProvider::DEEP_INFRA);
        $this->llmProvider = new LlmProvider(LlmProvider::DEEP_INFRA, 15 * 60);
        $this->llmModel = 'google/gemini-2.5-pro';

        // PoC : BEGIN
        // $this->buildVectorDatabase($out);
        $arguments = $this->search('bien-fondé du licenciement pour inaptitude');
        Log::debug($arguments);
        // PoC : END

        return;

        if (is_dir($in)) {
            $this->processDirectory($in, $out, $prompt);
        } elseif (is_file($in)) {
            $this->processFile($in, $in, $prompt);
        } else {
            throw new \Exception('Invalid input path : ' . $in);
        }
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

            file_put_contents($json, $array ? json_encode($array, JSON_PRETTY_PRINT) : $answer);
        }
        if (!file_exists($json)) {
            Log::warning("Skipping file $file : no json file.");
            return;
        }
    }

    private function buildVectorDatabase(string $output): void
    {
        $files = glob("{$output}/*.json");

        foreach ($files as $file) {

            $array = json_decode(file_get_contents($file), true);

            if (is_array($array)) {
                foreach ($array as $index => $json) {
                    if (isset($json['objet'])) {
                        $this->vectors->addDocument([
                            'file' => $file,
                            'index' => $index,
                            'objet' => $json['objet'],
                            'argumentation' => $json['argumentation'] ?? [],
                            'embedding' => $this->embed($json['objet']),
                        ]);
                    }
                }
            }
        }
    }

    private function search(string $text): array
    {
        return $this->vectors->search($this->embed($text));
    }

    private function embed(string $text)
    {
        return $this->embeddingProvider->execute($text)['data'][0]['embedding'];
    }
}
