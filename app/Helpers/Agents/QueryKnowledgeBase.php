<?php

namespace App\Helpers\Agents;

use App\Helpers\ApiUtilsFacade as ApiUtils;
use App\Helpers\DeepInfra;
use App\Models\Chunk;
use App\Models\ChunkTag;
use App\Models\File;
use App\Models\Prompt;
use App\Models\TimelineItem;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Parsedown;

class QueryKnowledgeBase extends AbstractAction
{
    static function schema(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "query_knowledge_base",
                "description" => "Answer questions related to cybersecurity guidelines or procedures. This includes inquiries about best practices, frameworks (such as ANSSI, NIST, OWASP, NIS2, DORA), or the Information Systems Security Policy (ISSP).",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "question" => [
                            "type" => "string",
                            "description" => "A user question related to information security.",
                        ],
                    ],
                    "required" => ["question"],
                    "additionalProperties" => false,
                ],
                "strict" => true,
            ],
        ];
    }

    public function __construct(User $user, string $threadId, array $messages, array $args = [])
    {
        parent::__construct($user, $threadId, $messages, $args);
    }

    function execute(): AbstractAction
    {
        $fallbackOnNextCollection = $this->args['fallback_on_next_collection'] ?? false;
        $question = htmlspecialchars($this->args['question'] ?? '', ENT_QUOTES, 'UTF-8');
        $json = $this->reformulateQuestion($question);
        $answer = Str::trim(Str::replace('I_DONT_KNOW', '', $this->queryChunks($json)));

        if (!empty($answer)) {
            $this->output = [
                'answer' => (new Parsedown)->text($answer),
                'sources' => collect(),
            ];
            return $this;
        }

        $response = ApiUtils::chat_manual_demo($this->threadId, null, $question, $fallbackOnNextCollection);

        if ($response['error']) {
            Log::error($response);
            $this->output = [
                'answer' => 'Sorry, an error occurred. Please try again later.',
                'sources' => collect(),
            ];
        } else {
            $this->output = [
                'answer' => $response['response'],
                'sources' => collect($response['context'] ?? []),
            ];
        }
        return $this;
    }

    public function html(): string
    {
        return $this->enhanceHtmlAnswerWithSources($this->output['answer'], $this->output['sources']);
    }

    public function text(): string
    {
        return $this->output['answer'];
    }

    public function markdown(): string
    {
        return $this->enhanceMarkdownAnswerWithSources($this->output['answer'], $this->output['sources']);
    }

    private function enhanceHtmlAnswerWithSources(string $answer, Collection $sources): string
    {
        $matches = [];
        // Extract: [12] from [[12]] or [[12] and [13]] from [[12],[13]]
        $isOk = preg_match_all("/\[\[\d+]]|\[\[\d+]|\[\d+]]/", $answer, $matches);
        if (!$isOk) {
            return Str::replace(["\n\n", "\n-"], "<br>", $answer);
        }
        $references = [];
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            /** @var array $tooltip */
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] == $id)->first();
            /** @var Chunk $chunk */
            $chunk = Chunk::find($id);
            /** @var File $file */
            $file = $chunk?->file()->first();
            $src = $file ? "<a href=\"{$file->downloadUrl()}\" style=\"text-decoration:none;color:black\">{$file->name_normalized}.{$file->extension}</a>, p. {$chunk->page}" : "";
            if (Str::startsWith($tooltip['text'] ?? '', 'ESSENTIAL DIRECTIVE')) {
                $color = '#1DD288';
            } else if (Str::startsWith($tooltip['text'] ?? '', 'STANDARD DIRECTIVE')) {
                $color = '#C5C3C3';
            } else if (Str::startsWith($tooltip['text'] ?? '', 'ADVANCED DIRECTIVE')) {
                $color = '#FDC99D';
            } else {
                $color = '#F8B500';
            }
            $tt = $tooltip['text'] ?? ($chunk?->text ?? '');
            $answer = Str::replace($ref, "<b style=\"color:{$color}\">[{$id}]</b>", $answer);
            $references[$id] = "
              <li style=\"padding:0;margin-bottom:0.25rem\">
                <b style=\"color:{$color}\">[{$id}]</b>&nbsp;
                <div class=\"cb-tooltip-list\">
                  {$src}
                  <span class=\"cb-tooltiptext cb-tooltip-list-top\" style=\"background-color:{$color};color:#444;\">
                    {$tt}
                  </span>
                </div>
              </li>
            ";
        }
        ksort($references);
        $answer = "{$answer}<br><br><b>Sources :</b><ul>" . collect($references)->values()->join("") . "</ul>";
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }

    private function enhanceMarkdownAnswerWithSources(string $answer, Collection $sources): string
    {
        $matches = [];
        // Extract: [12] from [[12]] or [[12] and [13]] from [[12],[13]]
        $isOk = preg_match_all("/\[\[\d+]]|\[\[\d+]|\[\d+]]/", $answer, $matches);
        if (!$isOk) {
            return Str::replace(["\n\n", "\n-"], "<br>", $answer);
        }
        $references = [];
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            /** @var array $tooltip */
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] == $id)->first();
            /** @var Chunk $chunk */
            $chunk = Chunk::find($id);
            /** @var File $file */
            $file = $chunk?->file()->first();
            $src = $file ? "({$file->name_normalized}.{$file->extension})[{$file->downloadUrl()}], p. {$chunk->page}" : "";
            $tt = $tooltip['text'] ?? ($chunk?->text ?? '');
            $answer = Str::replace($ref, "**[{$id}]**", $answer);
            $references[$id] = "<li>**[{$id}]** {$src}: {$tt}</li>";
        }
        ksort($references);
        $answer = "{$answer}\n\n**Sources:**\n<ul>" . collect($references)->values()->join("") . "</ul>";
        return Str::replace(["\n\n", "\n-"], "\n", $answer);
    }

    private function reformulateQuestion(string $question): array
    {
        $prompt = Prompt::where('created_by', $this->user->id)->where('name', 'default_reformulate_question')->firstOrfail();
        $prompt->template = Str::replace('{QUESTION}', $question, $prompt->template);
        $response = DeepInfra::execute($prompt->template, 'Qwen/Qwen3-30B-A3B');
        $answer = $response['choices'][0]['message']['content'] ?? '';
        // Log::debug("[2] answer : {$answer}");
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
        return json_decode($answer, true);
    }

    private function queryChunks(array $json): string
    {
        Log::debug($json);

        $memos = TimelineItem::fetchNotes($this->user->id, null, null, 0)
            ->map(function (TimelineItem $note) {
                $attributes = $note->attributes();
                $subject = $attributes['subject'] ?? 'Unknown subject';
                $body = $attributes['body'] ?? '';
                return "## {$note->timestamp->format('Y-m-d H:i:s')}\n\n### {$subject}\n\n{$body}";
            })
            ->join("\n\n");

        if (empty($memos) && (empty($json) || (empty($json['keywords_fr']) && empty($json['keywords_en'])))) {
            return '';
        }

        $start = microtime(true);
        /** @var array<string> $englishKeywords */
        $englishKeywords = $this->combine($json['keywords_en']);
        /** @var array<string> $frenchKeywords */
        $frenchKeywords = $this->combine($json['keywords_fr']);
        /** @var array<int> $englishCollections */
        $englishCollections = \App\Models\Collection::query()
            ->where('cb_collections.is_deleted', false)
            ->where(function ($query) {
                $query->where('cb_collections.name', 'like', "%lgen") // see YnhFramework::collectionName
                ->orWhere('cb_collections.name', 'not like', '%lg%');
            })
            ->orderBy('cb_collections.priority')
            ->orderBy('cb_collections.name')
            ->get()
            ->pluck('id')
            ->toArray();
        /** @var array<int> $frenchCollections */
        $frenchCollections = \App\Models\Collection::query()
            ->where('cb_collections.is_deleted', false)
            ->where(function ($query) {
                $query->where('cb_collections.name', 'like', "%lgfr") // see YnhFramework::collectionName
                ->orWhere('cb_collections.name', 'not like', '%lg%');
            })
            ->orderBy('cb_collections.priority')
            ->orderBy('cb_collections.name')
            ->get()
            ->pluck('id')
            ->toArray();

        $chunks = collect();

        foreach ($englishKeywords as $keywords) {
            try {
                $start2 = microtime(true);
                $results = Chunk::search($keywords)
                    ->whereIn('collection_id', $englishCollections)
                    ->paginate(10)
                    ->getCollection();
                $chunks = $chunks->merge($results);
                $stop2 = microtime(true);
                Log::debug("[EN] Search for '{$keywords}' took " . ((int)ceil($stop2 - $start2)) . " seconds and returned {$results->count()} results");
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            if ($chunks->isNotEmpty()) {
                break;
            }
        }
        foreach ($frenchKeywords as $keywords) {
            try {
                $start2 = microtime(true);
                $results = Chunk::search($keywords)
                    ->whereIn('collection_id', $frenchCollections)
                    ->paginate(10)
                    ->getCollection();
                $chunks = $chunks->merge($results);
                $stop2 = microtime(true);
                Log::debug("[FR] Search for '{$keywords}' took " . ((int)ceil($stop2 - $start2)) . " seconds and returned {$results->count()} results");
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            if ($chunks->isNotEmpty()) {
                break;
            }
        }

        $stop = microtime(true);
        Log::debug("Search took " . ((int)ceil($stop - $start)) . " seconds and returned {$chunks->count()} results");

        $notes = $chunks
            ->groupBy('text') // remove duplicates
            ->map(fn(Collection $group) => $group->sortByDesc('__tntSearchScore__')->first()) // the higher the better
            ->values() // associative array => array
            ->sortByDesc('__tntSearchScore__')
            ->sortBy('priority')
            ->take(10)
            ->map(function (Chunk $chunk) {

                $text = Str::replace("#", "", $chunk->text);

                $tags = ChunkTag::where('chunk_id', '=', $chunk->id)
                    ->orderBy('id')
                    ->get()
                    ->map(fn(ChunkTag $tag) => $tag->tag)
                    ->join(", ");

                $tags = empty($tags) ? 'n/a' : $tags;

                return "## Note {$chunk->id}\n\n{$text}\n\nTags: {$tags}\nScore: {$chunk->{'__tntSearchScore__'}}";
            })
            ->join("\n\n");

        if (empty($notes) && empty($memos)) {
            return '';
        }

        $prompt = Prompt::where('created_by', $this->user->id)->where('name', 'default_answer_question')->firstOrfail();
        $prompt->template = Str::replace('{LANGUAGE}', $json['lang'], $prompt->template);
        $prompt->template = Str::replace('{MEMOS}', $memos, $prompt->template);
        $prompt->template = Str::replace('{NOTES}', $notes, $prompt->template);
        $prompt->template = Str::replace('{QUESTION}', $json['question_en'], $prompt->template);
        Log::debug($prompt->template);
        $response = DeepInfra::execute($prompt->template, 'Qwen/Qwen3-30B-A3B');
        $answer = $response['choices'][0]['message']['content'] ?? '';
        Log::debug("[3] answer : {$answer}");
        return preg_replace('/<think>.*?<\/think>/s', '', $answer);
    }

    private function combine(array $arrays): array
    {
        if (empty($arrays)) {
            return [];
        }

        /** @var array<array<string>> $combinations */
        $combinations = array_map(fn(string $word) => [$word], $arrays[0]);

        for ($i = 1; $i < count($arrays); $i++) {

            /** @var array<string> $cur */
            $cur = $arrays[$i];
            $new = [];

            foreach ($combinations as $existing) {
                foreach ($cur as $word) {
                    $new[] = array_merge($existing, [$word]);
                }
            }
            $combinations = $new;
        }
        return array_map(fn(array $combination) => implode(" ", $combination), $combinations);
    }
}
