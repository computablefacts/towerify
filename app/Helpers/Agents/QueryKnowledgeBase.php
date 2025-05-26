<?php

namespace App\Helpers\Agents;

use App\Helpers\ApiUtilsFacade as ApiUtils;
use App\Models\Chunk;
use App\Models\File;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    public function __construct(User $user, string $threadId, array $args = [])
    {
        parent::__construct($user, $threadId, $args);
    }

    function execute(): AbstractAction
    {
        $fallbackOnNextCollection = $this->args['fallback_on_next_collection'] ?? false;
        $question = htmlspecialchars($this->args['question'] ?? '', ENT_QUOTES, 'UTF-8');
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
        return $this->enhanceAnswerWithSources($this->output['answer'], $this->output['sources']);
    }

    public function text(): string
    {
        return $this->output['answer'];
    }

    public function markdown(): string
    {
        return $this->enhanceAnswerWithSources2($this->output['answer'], $this->output['sources']);
    }

    private function enhanceAnswerWithSources(string $answer, Collection $sources): string
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
            if ($tooltip) {
                if (Str::startsWith($tooltip['text'], 'ESSENTIAL DIRECTIVE')) {
                    $color = '#1DD288';
                } else if (Str::startsWith($tooltip['text'], 'STANDARD DIRECTIVE')) {
                    $color = '#C5C3C3';
                } else if (Str::startsWith($tooltip['text'], 'ADVANCED DIRECTIVE')) {
                    $color = '#FDC99D';
                } else {
                    $color = '#F8B500';
                }
                $answer = Str::replace($ref, "<b style=\"color:{$color}\">[{$id}]</b>", $answer);
                $references[$id] = "
                  <li style=\"padding:0;margin-bottom:0.25rem\">
                    <b style=\"color:{$color}\">[{$id}]</b>&nbsp;
                    <div class=\"cb-tooltip-list\">
                      {$src}
                      <span class=\"cb-tooltiptext cb-tooltip-list-top\" style=\"background-color:{$color};color:#444;\">
                        {$tooltip['text']}
                      </span>
                    </div>
                  </li>
                ";
            }
        }
        ksort($references);
        $answer = "{$answer}<br><br><b>Sources :</b><ul>" . collect($references)->values()->join("") . "</ul>";
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }

    private function enhanceAnswerWithSources2(string $answer, Collection $sources): string
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
            if ($tooltip) {
                $answer = Str::replace($ref, "**[{$id}]**", $answer);
                $references[$id] = "<li>**[{$id}]** {$src}: {$tooltip['text']}</li>";
            }
        }
        ksort($references);
        $answer = "{$answer}\n\n**Sources:**\n<ul>" . collect($references)->values()->join("") . "</ul>";
        return Str::replace(["\n\n", "\n-"], "\n", $answer);
    }
}
