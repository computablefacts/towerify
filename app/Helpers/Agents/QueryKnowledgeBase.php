<?php

namespace App\Helpers\Agents;

use App\Helpers\ApiUtilsFacade as ApiUtils;
use App\Helpers\DeepInfra;
use App\Models\Chunk;
use App\Models\ChunkTag;
use App\Models\File;
use App\Models\TimelineItem;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public function __construct(User $user, string $threadId, array $messages, array $args = [])
    {
        parent::__construct($user, $threadId, $messages, $args);
    }

    function execute(): AbstractAction
    {
        $fallbackOnNextCollection = $this->args['fallback_on_next_collection'] ?? false;
        $question = htmlspecialchars($this->args['question'] ?? '', ENT_QUOTES, 'UTF-8');
        $notes = TimelineItem::fetchNotes($this->user->id, null, null, 0)
            ->map(function (TimelineItem $note) {
                $attributes = $note->attributes();
                $subject = $attributes['subject'] ?? 'Unknown subject';
                $body = $attributes['body'] ?? '';
                return "## {$note->timestamp->format('Y-m-d H:i:s')}\n\n### {$subject}\n\n{$body}";
            })
            ->join("\n\n");

        if (!empty($notes)) {

            $prompt = "
                Use the user's notes below to answer the user's question.
                If the information in the notes is insufficient to determine the answer, respond with 'I_DONT_KNOW'.
                Ensure your answer is in plain text format without any Markdown or HTML formatting.

                # User's Notes
                
                {$notes}
                
                # User's Question
                
                {$question}
            ";

            /* $messages = $this->messages;
            $messages[] = [
                'role' => RoleEnum::USER->value,
                'content' => $prompt,
            ];
            $response = DeepInfra::executeEx($messages, 'Qwen/Qwen3-30B-A3B'); */
            $response = DeepInfra::execute($prompt, 'Qwen/Qwen3-30B-A3B');
            $answer = $response['choices'][0]['message']['content'] ?? '';
            // Log::debug("[1] answer : {$answer}");
            $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);

            if (!empty($answer) && !Str::contains($answer, 'I_DONT_KNOW')) {
                $this->output = [
                    'answer' => $answer,
                    'sources' => collect(),
                ];
                return $this;
            }
        }

        $prompt = "
            You are tasked with creating an effective list of alternative questions from the user's question.
            
            To create an effective list of questions, follow these steps:
            1. Expand the user input, considering the context.
            2. Generate paraphrased versions of the expanded questions.
            
            The output should be a JSON with the following attributes:
            - question: the user's original question.
            - question_fr: the user's question in French.
            - question_en: the user's question in English.
            - paraphrased_fr: a list of paraphrased questions in French.
            - paraphrased_en: a list of paraphrased questions in English.
            - expanded_fr: a list of expanded questions in French.
            - expanded_en: a list of expanded questions in English.
            
            For example, if the user's question is \"How to create a complex password?\", a possible output could be:
            {
                \"question\": \"How to create a complex password?\",
                \"question_en\": \"How to create a complex password?\",
                \"question_fr\": \"Comment créer un mot de passe complexe ?\",
                \"paraphrased_en\": [
                    \"What are the steps to generate a strong password?\",
                    \"Can you guide me on making a secure password?\",
                    \"How do I come up with a hard-to-crack password?\"
                ],
                \"paraphrased_fr\": [
                    \"Quelles sont les étapes pour générer un mot de passe robuste ?\",
                    \"Pouvez-vous me guider pour créer un mot de passe sécurisé ?\",
                    \"Comment puis-je concevoir un mot de passe difficile à pirater ?\"
                ],
                \"expanded_en\": [
                    \"What are the best practices for creating a password that is difficult to guess?\",
                    \"How can I ensure my password is secure against hacking attempts?\",
                    \"What tools or methods can assist in generating a complex password?\",
                    \"Why is it important to have a complex password for online security?\"
                ],
                \"expanded_fr\": [
                    \"Quelles sont les meilleures pratiques pour créer un mot de passe difficile à deviner ?\",
                    \"Comment puis-je m'assurer que mon mot de passe est sécurisé contre les tentatives de piratage ?\",
                    \"Quels outils ou méthodes peuvent aider à générer un mot de passe complexe ?\",
                    \"Pourquoi est-il important d'avoir un mot de passe complexe pour la sécurité en ligne ?\"
                ]
            }

            Ensure your answer is in plain text format without any Markdown or HTML formatting.
            The user's question is:
            
            {$question}       
        ";

        $response = DeepInfra::execute($prompt, 'Qwen/Qwen3-30B-A3B');
        $answer = $response['choices'][0]['message']['content'] ?? '';
        // Log::debug("[2] answer : {$answer}");
        $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);
        $json = json_decode($answer, true);
        Log::debug($json);

        if (!empty($json) && (!empty($json['paraphrased_fr']) || !empty($json['paraphrased_en'])) && (!empty($json['expanded_fr']) || !empty($json['expanded_en']))) {

            $mode = "IN NATURAL LANGUAGE MODE"; // or WITH QUERY EXPANSION

            $exprFr = collect($json['paraphrased_fr'])
                ->concat($json['expanded_fr'])
                ->map(fn(string $paraphrased) => Str::replace("'", "\'", $paraphrased))
                ->map(fn(string $paraphrased) => "MATCH(cb_chunks.text) AGAINST ('{$paraphrased}' {$mode})")
                ->join(" OR ");

            $scoreFr = collect($json['paraphrased_fr'])
                ->concat($json['expanded_fr'])
                ->map(fn(string $paraphrased) => Str::replace("'", "\'", $paraphrased))
                ->map(fn(string $paraphrased) => "MATCH(cb_chunks.text) AGAINST ('{$paraphrased}' {$mode})")
                ->join(" + ");

            $exprEn = collect($json['paraphrased_en'])
                ->concat($json['expanded_en'])
                ->map(fn(string $paraphrased) => Str::replace("'", "\'", $paraphrased))
                ->map(fn(string $paraphrased) => "MATCH(cb_chunks.text) AGAINST ('{$paraphrased}' {$mode})")
                ->join(" OR ");

            $scoreEn = collect($json['paraphrased_en'])
                ->concat($json['expanded_en'])
                ->map(fn(string $paraphrased) => Str::replace("'", "\'", $paraphrased))
                ->map(fn(string $paraphrased) => "MATCH(cb_chunks.text) AGAINST ('{$paraphrased}' {$mode})")
                ->join(" + ");

            $filterByTenantId = $this->user->tenant_id ? "AND (users.tenant_id IS NULL OR users.tenant_id = {$this->user->tenant_id})" : "";
            $filterByCustomerId = $this->user->customer_id ? "AND (users.customer_id IS NULL OR users.customer_id = {$this->user->customer_id})" : "";

            $query = "
                SELECT * 
                FROM (
                    SELECT
                      'fr' AS lang,
                      MAX(({$scoreFr}) / (cb_collections.priority + 0.01)) AS score,
                      MAX(cb_chunks.id) AS id,
                      cb_chunks.text
                    FROM cb_chunks
                    INNER JOIN cb_files ON cb_chunks.file_id = cb_files.id
                    INNER JOIN cb_collections ON cb_files.collection_id = cb_collections.id
                    INNER JOIN users ON cb_collections.created_by = users.id
                    WHERE cb_chunks.is_deleted = 0
                    AND cb_files.is_deleted = 0
                    AND cb_collections.is_deleted = 0
                    AND (cb_collections.name LIKE '%lgfr' OR cb_collections.name NOT LIKE '%lg%')
                    AND ({$exprFr})
                    {$filterByTenantId}
                    {$filterByCustomerId}
                    GROUP BY lang, text
                    
                    UNION
                    
                    SELECT
                      'en' AS lang,
                      MAX(({$scoreEn}) / (cb_collections.priority + 0.01)) AS score,
                      MAX(cb_chunks.id) AS id,
                      cb_chunks.text
                    FROM cb_chunks
                    INNER JOIN cb_files ON cb_chunks.file_id = cb_files.id
                    INNER JOIN cb_collections ON cb_files.collection_id = cb_collections.id
                    INNER JOIN users ON cb_collections.created_by = users.id
                    WHERE cb_chunks.is_deleted = 0 
                    AND cb_files.is_deleted = 0
                    AND cb_collections.is_deleted = 0
                    AND (cb_collections.name LIKE '%lgen' OR cb_collections.name NOT LIKE '%lg%')
                    AND ({$exprEn})
                    {$filterByTenantId}
                    {$filterByCustomerId}
                    GROUP BY lang, text
                ) AS t
                ORDER BY t.score DESC
                LIMIT 10
            ";

            // Log::debug($query);

            $idx = 0;
            $notes = collect(DB::select($query))
                ->map(function (object $chunk) use (&$idx) {

                    ++$idx;
                    $tags = ChunkTag::where('chunk_id', '=', $chunk->id)
                        ->orderBy('id')
                        ->get()
                        ->map(fn(ChunkTag $tag) => $tag->tag)
                        ->join("\n- ");

                    return "
                        ## Note {$idx}
                        
                        ### Metadata
                        
                        Id: {$chunk->id}
                        Score: {$chunk->score}
                        Lang: {$chunk->lang}
                        Tags:
                        - {$tags}
                        
                        ### Content
                        
                        {$chunk->text}
                    ";
                })
                ->join("\n\n");

            $prompt = "
                Use the user's notes below to answer the user's question.
                The user's notes are listed below in order of relevance, from most (Note 1) to least relevant (Note {$idx}).
                When writing, insert the 'Id' of a note in double square brackets (e.g., [[Id]]) immediately after the text it refers to, but only on its first use.
                If the information in the notes is insufficient to determine the answer, respond with 'I_DONT_KNOW'.
                Ensure your answer is in plain text format without any Markdown or HTML formatting.
                
                # User's Notes
                
                {$notes}
                
                # User's Question
                
                {$json['question']}
            ";

            Log::debug($prompt);

            $response = DeepInfra::execute($prompt, 'Qwen/Qwen3-30B-A3B');
            $answer = $response['choices'][0]['message']['content'] ?? '';
            Log::debug("[3] answer : {$answer}");
            $answer = preg_replace('/<think>.*?<\/think>/s', '', $answer);

            if (!empty($answer) && !Str::contains($answer, 'I_DONT_KNOW')) {
                $this->output = [
                    'answer' => $answer,
                    'sources' => collect(),
                ];
                return $this;
            }
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
