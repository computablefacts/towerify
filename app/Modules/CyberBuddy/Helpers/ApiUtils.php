<?php

namespace App\Modules\CyberBuddy\Helpers;

use App\Modules\CyberBuddy\Exceptions\ApiException;
use App\Modules\CyberBuddy\Models\Prompt;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class ApiUtils
{
    const REQUEST_TIMEOUT = 300; // 5 minutes

    public function file_input(string $client, string $url): array
    {
        $response = $this->post('/api/file-input', [
            'url' => $url,
            'client' => $client,
        ]);
        return $this->json($response);
    }

    public function delete_collection(string $collectionName): array
    {
        $response = $this->post('/delete_collection', [
            'collection_name' => $collectionName
        ]);
        return $this->json($response);
    }

    public function import_chunks(array $chunks, string $collectionName): array
    {
        $response = $this->post('/import_chunks', [
            'chunks' => $chunks,
            'collection_name' => $collectionName
        ]);
        return $this->json($response);
    }

    public function delete_chunks(array $uids, string $collectionName): array
    {
        $response = $this->post('/delete_chunks', [
            'collection_name' => $collectionName,
            'uids' => $uids
        ]);
        return $this->json($response);
    }

    /** @deprecated */
    public function ask_chunks_demo(string $collection, string $question): array
    {
        /** @var Prompt $prompt */
        $prompt = Prompt::where('name', 'default_debugger')->firstOrfail();
        return $this->ask_chunks($question, $collection, $prompt->template, true, true, 'fr');
    }

    public function ask_chunks(string $question, string $collectionName, string $prompt, bool $rerankings = true, bool $showContext = true, string $lang = 'en', int $maxDocsUsed = 5): array
    {
        $response = $this->post('/ask_chunks', [
            'question' => $question,
            'collection_name' => $collectionName,
            'prompt' => $prompt,
            'reranking' => $rerankings,
            'show_context' => $showContext,
            'lang' => $lang,
            'max_docs_used' => $maxDocsUsed
        ]);
        return $this->json($response);
    }

    public function search_chunks(string $question, string $collectionName, bool $rerankings = true, bool $showContext = true, string $lang = 'en', int $maxDocsUsed = 5): array
    {
        $response = $this->post('/search_chunks', [
            'question' => $question,
            'collection_name' => $collectionName,
            'reranking' => $rerankings,
            'show_context' => $showContext,
            'lang' => $lang,
            'max_docs_used' => $maxDocsUsed
        ]);
        return $this->json($response);
    }

    public function import_qas(array $qaList, string $collectionName): array
    {
        $response = $this->post('/import_qas', [
            'qa_list' => $qaList,
            'collection_name' => $collectionName
        ]);
        return $this->json($response);
    }

    public function delete_qas(array $uids, string $collectionName): array
    {
        $response = $this->post('/delete_qas', [
            'collection_name' => $collectionName,
            'uids' => $uids
        ]);
        return $this->json($response);
    }

    public function search_qas(string $question, string $collectionName, int $maxResponse = 5, float $weight = 1.0): array
    {
        $response = $this->post('/search_qas', [
            'question' => $question,
            'collection_name' => $collectionName,
            'max_responses' => $maxResponse,
            'weight' => $weight
        ]);
        return $this->json($response);
    }

    public function capsule(array $facts, string $title, string $prompt): array
    {
        $response = $this->post('/capsule', [
            'facts' => $facts,
            'title' => $title,
            'prompt' => $prompt
        ]);
        return $this->json($response);
    }

    /** @deprecated */
    public function chat_manual_demo(string $historyKey, string $collection, string $question): array
    {
        /** @var Prompt $prompt */
        $prompt = Prompt::where('name', 'default_chat')->firstOrfail();
        $promptHistory = Prompt::where('name', 'default_chat_history')->firstOrfail();
        return $this->chat_manual($question, $collection, $historyKey, $prompt->template, $promptHistory->template, 10, 'fr');
    }

    public function chat_manual(string $question, string $collectionName, string $historyKey, string $prompt, string $historyPrompt, int $maxDocsUsed = 5, string $lang = 'en'): array
    {
        $response = $this->post('/chat_manual', [
            'question' => $question,
            'collection_name' => $collectionName,
            'history_key' => $historyKey,
            'prompt' => $prompt,
            'history_prompt' => $historyPrompt,
            'max_docs_used' => $maxDocsUsed,
            'lang' => $lang,
            'show_context' => true
        ]);
        return $this->json($response);
    }

    private function get($endpoint, $json): ResponseInterface
    {
        $url = Config::get('towerify.cyberbuddy.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
                /* 'auth' => [
                    config('towerify.cyberbuddy.api_username'),
                    config('towerify.cyberbuddy.api_password')
                ] */
            ]);
            return $client->get($url, $this->httpHeaders($json));
        } catch (ClientException $exception) {
            throw new ApiException('API client problem (code: ' . $exception->getCode() . ', url: ' . $url . ')', $exception->getCode(), $exception);
        } catch (Exception $exception) {
            throw new ApiException('API problem: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private function post($endpoint, $json): ResponseInterface
    {
        $url = Config::get('towerify.cyberbuddy.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
                /* 'auth' => [
                    config('towerify.cyberbuddy.api_username'),
                    config('towerify.cyberbuddy.api_password')
                ] */
            ]);
            return $client->post($url, $this->httpHeaders($json));
        } catch (ClientException $exception) {
            throw new ApiException('API client problem (code: ' . $exception->getCode() . ', url: ' . $url . ')', $exception->getCode(), $exception);
        } catch (Exception $exception) {
            throw new ApiException('API problem: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private function httpHeaders($json): array
    {
        return [

            'json' => $json,

            // Set content type
            'Content-Type' => 'application/json',

            // No SSL verification. Guzzle try to verify even a HTTP request (so it fails)
            // See http://docs.guzzlephp.org/en/latest/request-options.html#verify
            'verify' => false,

            // With Exception on HTTP error (400, 500, etc)
            // See http://docs.guzzlephp.org/en/latest/request-options.html#http-errors
            'http_errors' => true,
        ];
    }

    private function json(ResponseInterface $response): array
    {
        $body = $this->body($response);
        return $this->isOk($response, $body) ? $this->results($response, $body) : [];
    }

    private function body(ResponseInterface $response): array
    {
        ini_set("memory_limit", "-1");
        if (version_compare(PHP_VERSION, '5.4.0', '>=')
            && !(defined('JSON_C_VERSION')
                && PHP_INT_SIZE > 4)
        ) {
            /** In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
             * to specify that large ints (like Steam Transaction IDs) should be treated as
             * strings, rather than the PHP default behaviour of converting them to floats.
             */
            return json_decode($response->getBody(), true, 512, JSON_BIGINT_AS_STRING);
        }

        /** Not all servers will support that, however, so for older versions we must
         * manually detect large ints in the JSON string and quote them (thus converting
         *them to strings) before decoding, hence the preg_replace() call.
         */
        $max_int_length = strlen((string)PHP_INT_MAX) - 1;
        $json_without_bigints =
            preg_replace('/:\s*(-?\d{' . $max_int_length . ',})/', ': "$1"', $response->getBody());
        return json_decode($json_without_bigints, true);
    }

    private function isOk(ResponseInterface $response, array $body): bool
    {
        if ($response->getStatusCode() === 200) {
            return true;
        }
        throw new ApiException('Fast API problem: ' . ($body['message'] ?? 'unknown'));
    }

    private function results(ResponseInterface $response, array $body): array
    {
        return $this->isOk($response, $body) && !empty($body) ? $body : [];
    }
}
