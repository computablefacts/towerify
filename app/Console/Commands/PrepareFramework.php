<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class PrepareFramework extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'framework:prepare {input}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert a Cyber framework to a list of chunks.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (is_dir($this->argument('input'))) {
            $this->processDirectory($this->argument('input'));
        } elseif (is_file($this->argument('input'))) {
            $this->processFile($this->argument('input'));
        } else {
            throw new \Exception('Invalid input path : ' . $this->argument('input'));
        }
    }

    private function processDirectory(string $dir): void
    {
        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        if (count($ffs) < 1) {
            return;
        }
        foreach ($ffs as $ff) {
            if (is_dir($dir . '/' . $ff)) {
                $this->processDirectory($dir . '/' . $ff);
            } else if (is_file($dir . '/' . $ff)) {
                $this->processFile($dir . '/' . $ff);
            }
        }
    }

    private function processFile(string $file): void
    {
        if (!Str::endsWith($file, '.yaml')) {
            return;
        }

        $yaml = File::get($file);
        $json = Yaml::parse($yaml);

        if (!isset($json['objects']['framework'])) {
            return;
        }

        $infos = [
            'locale' => $json['locale'],
            'name' => $json['name'],
            'description' => $json['description'],
            'copyright' => $json['copyright'],
            'version' => $json['version'],
            'provider' => $json['provider'],
            'file' => Str::replace('.yaml', '.jsonl', basename($file)),
        ];

        file_put_contents(Str::replace('.yaml', '.json', $file), json_encode($infos) . PHP_EOL, FILE_APPEND);

        $framework = $json['objects']['framework'];
        $requirements = $framework['requirement_nodes'];
        $tree = [];
        $urnToNodeMap = [];

        foreach ($requirements as $node) {
            $urn = $node['urn'];
            $urnToNodeMap[$urn] = array_merge($node, ['children' => []]);
        }
        foreach ($requirements as $node) {
            if (empty($node['parent_urn'])) {
                $tree[] = &$urnToNodeMap[$node['urn']];
            } else {
                $parentUrn = $node['parent_urn'];
                if (isset($urnToNodeMap[$parentUrn])) {
                    $urnToNodeMap[$parentUrn]['children'][] = &$urnToNodeMap[$node['urn']];
                }
            }
        }
        foreach ($tree as $node) {
            $chunks = [];
            $this->generateChunk($node, [], $chunks);
            foreach ($chunks as $chunk) {
                file_put_contents(Str::replace('.yaml', '.jsonl', $file), json_encode($chunk) . PHP_EOL, FILE_APPEND);
            }
        }
    }

    private function generateChunk(array $node, array $parentTags, array &$chunks): void
    {
        if (isset($node['name'])) {
            $currentTags = array_merge($parentTags, [trim($node['name'])]);
        } else {
            $currentTags = $parentTags;
        }
        if (empty($node['children'])) {
            $chunks[] = [
                'page' => 1,
                'tags' => $currentTags,
                'text' => trim($node['description'] ?? 'No description available'),
            ];
        } else {
            foreach ($node['children'] as $childNode) {
                $this->generateChunk($childNode, $currentTags, $chunks);
            }
        }
    }
}
