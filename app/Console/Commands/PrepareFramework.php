<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class PrepareFramework extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'framework:prepare {input} {output}';

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
        $yaml = File::get($this->argument('input'));
        $json = Yaml::parse($yaml);
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
                file_put_contents($this->argument('output'), json_encode($chunk) . PHP_EOL, FILE_APPEND);
            }
        }
    }

    private function generateChunk(array $node, array $parentTags, array &$chunks): void
    {
        $currentTags = array_merge($parentTags, [$node['name'] ?? 'Unnamed Node']);
        if (empty($node['children'])) {
            $chunks[] = [
                'page' => 1,
                'tags' => $currentTags,
                'text' => $node['description'] ?? 'No description available'
            ];
        } else {
            foreach ($node['children'] as $childNode) {
                $this->generateChunk($childNode, $currentTags, $chunks);
            }
        }
    }
}
