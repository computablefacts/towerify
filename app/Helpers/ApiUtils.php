<?php

namespace App\Helpers;

use App\Models\Collection;
use App\Models\Prompt;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiUtils
{
    public function whisper(string $url, string $lang = 'fr')
    {
        return $this->post('/api/whisper', [
            'url' => $url,
            'lang' => $lang,
        ]);
    }

    public function file_input(string $client, string $url, ?string $filename = null): array
    {
        return $this->post('/api/file-input', [
            'url' => $url,
            'client' => $client,
            'filename' => $filename,
        ]);
    }

    public function delete_collection(string $collectionName): array
    {
        return $this->post('/delete_collection', [
            'collection_name' => $collectionName
        ]);
    }

    public function import_chunks(array $chunks, string $collectionName): array
    {
        return $this->post('/import_chunks', [
            'chunks' => $chunks,
            'collection_name' => $collectionName
        ]);
    }

    public function delete_chunks(array $uids, string $collectionName): array
    {
        return $this->post('/delete_chunks', [
            'collection_name' => $collectionName,
            'uids' => $uids
        ]);
    }

    /** @deprecated */
    public function ask_chunks_demo(?string $collection, string $question, string $prompt = ""): array
    {
        return $this->ask_chunks($question, $collection, $prompt, true, true, 'fr');
    }

    public function ask_chunks(string $question, ?string $collectionName, ?string $prompt = null, bool $rerankings = true, bool $showContext = true, string $lang = 'en', int $maxDocsUsed = 10, bool $generateQuestion = false): array
    {
        if (empty($collectionName)) {
            $collections = Collection::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();
        } else {
            $collections = Collection::where('name', '<>', $collectionName)
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();
            array_unshift($collections, $collectionName);
        }
        return $this->post('/ask_chunks', [
            'question' => $question,
            'generate_question' => $generateQuestion,
            'collections' => $collections,
            'collection_name' => $collections[0],
            'prompt' => Str::replace("\r", "", $prompt),
            'reranking' => $rerankings,
            'show_context' => $showContext,
            'lang' => $lang,
            'max_docs_used' => $maxDocsUsed
        ]);
    }

    public function search_chunks(string $question, ?string $collectionName, bool $rerankings = true, bool $showContext = true, string $lang = 'en', int $maxDocsUsed = 10): array
    {
        if (empty($collectionName)) {
            $collections = Collection::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();
        } else {
            $collections = Collection::where('name', '<>', $collectionName)
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();
            array_unshift($collections, $collectionName);
        }
        return $this->post('/search_chunks', [
            'question' => $question,
            'collections' => $collections,
            'collection_name' => $collections[0],
            'reranking' => $rerankings,
            'show_context' => $showContext,
            'lang' => $lang,
            'max_docs_used' => $maxDocsUsed
        ]);
    }

    public function import_qas(array $qaList, string $collectionName): array
    {
        return $this->post('/import_qas', [
            'qa_list' => $qaList,
            'collection_name' => $collectionName
        ]);
    }

    public function delete_qas(array $uids, string $collectionName): array
    {
        return $this->post('/delete_qas', [
            'collection_name' => $collectionName,
            'uids' => $uids
        ]);
    }

    public function search_qas(string $question, string $collectionName, int $maxResponse = 5, float $weight = 1.0): array
    {
        return $this->post('/search_qas', [
            'question' => $question,
            'collection_name' => $collectionName,
            'max_responses' => $maxResponse,
            'weight' => $weight
        ]);
    }

    public function capsule(array $facts, string $title, string $prompt): array
    {
        return $this->post('/capsule', [
            'facts' => $facts,
            'title' => $title,
            'prompt' => Str::replace("\r", "", $prompt)
        ]);
    }

    /** @deprecated */
    public function chat_manual_demo(string $historyKey, ?string $collection, string $question): array
    {
        /** @var Prompt $prompt */
        $prompt = Prompt::where('name', 'default_chat')->firstOrfail();
        $promptHistory = Prompt::where('name', 'default_chat_history')->firstOrfail();
        return $this->chat_manual($question, $collection, $historyKey, $prompt->template, $promptHistory->template, 10, 'fr');
    }

    public function chat_manual(string $question, ?string $collectionName, string $historyKey, string $prompt, string $historyPrompt, int $maxDocsUsed = 10, string $lang = 'en'): array
    {
        if (empty($collectionName)) {
            $collections = Collection::query()
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();
        } else {
            $collections = Collection::where('name', '<>', $collectionName)
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();
            array_unshift($collections, $collectionName);
        }
        return $this->post('/chat_manual', [
            'question' => $question,
            'collections' => $collections,
            'collection_name' => $collections[0],
            'history_key' => $historyKey,
            'prompt' => Str::replace("\r", "", $prompt),
            'history_prompt' => $historyPrompt,
            'max_docs_used' => $maxDocsUsed,
            'lang' => $lang,
            'show_context' => true
        ]);
    }

    public function generate_from_template(string $template, string $prompt, array $questionsAndAnswers): array
    {
        return $this->post('/generate_from_template', [
            'template' => $template,
            'qa_list' => $questionsAndAnswers,
            'prompt' => Str::replace("\r", "", $prompt),
        ]);
    }

    private function post($endpoint, $json): array
    {
        $url = Config::get('towerify.cyberbuddy.api') . $endpoint;

        $response = Http::timeout(180)
            ->withBasicAuth(
                config('towerify.cyberbuddy.api_username'),
                config('towerify.cyberbuddy.api_password')
            )->withHeaders([
                'Accept' => 'application/json',
            ])->post($url, $json);

        if ($response->successful()) {
            $json = $response->json();
            // Log::debug($json);
            return $json ?: [];
        }
        Log::error($response->body());
        return [];
    }
}
