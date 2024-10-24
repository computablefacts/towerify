<?php

namespace App\Modules\AdversaryMeter\Helpers;

use App\Modules\AdversaryMeter\Exceptions\ApiException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

class ApiUtils
{
    const REQUEST_TIMEOUT = 300; // 5 minutes

    public function checkport_public($host, $port, $protocol): array
    {
        $response = $this->post('/checkport', [
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol
        ]);
        return $this->json($response);
    }

    public function discover_public(string $domain): array
    {
        $response = $this->post('/discover', array_merge(
            [
                'domain' => $domain,
            ]
        ));
        $json = $this->json($response);
        if (isset($json['subdomains'])) {
            $json['subdomains'] = collect($json['subdomains'])
                ->filter(fn(string $subdomain) => !empty($subdomain))
                ->toArray();
        }
        return $json;
    }

    public function screenshot_public(string $domain): array
    {
        $response = $this->post('/screenshot', array_merge(
            [
                'url' => $domain,
            ]
        ));
        return $this->json($response);
    }

    public function start_scan_public($asset, $ip, $port, $protocol): array
    {
        $response = $this->post('/start_scan', array_merge(
            [
                'hostname' => $asset,
                'ip' => $ip,
                'port' => $port,
                'protocol' => $protocol,
                'client' => null,
                'tags' => [],
                'tests' => [],
            ]
        ));
        return $this->json($response);
    }

    public function task_masscan_public(string $host): array
    {
        $response = $this->post('/task-masscan', [
            'input' => [
                $host
            ]
        ]);
        return $this->json($response);
    }

    public function task_nmap_public(string $host): array
    {
        $response = $this->post('/task-nmap', [
            'input' => [
                $host
            ]
        ]);
        return $this->json($response);
    }

    public function task_status_public(string $id): array
    {
        $response = $this->get('/task_status/' . $id, []);
        return $this->json($response);
    }

    public function task_result_public(string $id): array
    {
        $response = $this->get('/task_result/' . $id, []);
        return $this->json($response);
    }

    public function task_start_scan_public($hostname, $ip, $port, $protocol, $tags): array
    {
        $response = $this->post('/start_scan', [
            "hostname" => $hostname,
            "ip" => $ip,
            "port" => $port,
            "protocol" => $protocol,
            "client" => "",
            "tags" => $tags
        ]);
        return $this->json($response);
    }

    public function task_get_scan_public(string $scanId): array
    {
        $response = $this->get('/get_scan/' . $scanId, []);
        return $this->json($response);
    }

    public function ip_geoloc_public(string $ip): array
    {
        $response = $this->post('/ipgeoloc', [
            'input' => $ip
        ]);
        return $this->json($response);
    }

    public function ip_whois_public(string $ip): array
    {
        $response = $this->post('/ipwhois', [
            'input' => $ip
        ]);
        return $this->json($response);
    }

    public function task_discover_full_public(array $urls): array
    {
        $response = $this->post('/discover-full-task', [
            'input' => $urls
        ]);
        return $this->json($response);
    }

    public function discover_from_ip_public(string $ip): array
    {
        $response = $this->post('/rapid_reverse_ip', [
            'input' => $ip
        ]);
        return $this->json($response);
    }

    public function external_ips(): array
    {
        $response = $this->external_ips_private();
        return $this->json($response);
    }

    public function matched_cves(): array
    {
        $response = $this->matched_cves_private();
        return $this->json($response);
    }

    private function external_ips_private(): ResponseInterface
    {
        // ../.. to remove /api/v1/ so go back to root.
        return $this->get('/../../sentinel_external_ips', []);
    }

    private function matched_cves_private(): ResponseInterface
    {
        // ../.. to remove /api/v1/ so go back to root.
        return $this->get('/../../cves-list', []);
    }

    private function get($endpoint, $json): ResponseInterface
    {
        $url = Config::get('towerify.adversarymeter.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
                'auth' => [
                    config('towerify.adversarymeter.api_username'),
                    config('towerify.adversarymeter.api_password')
                ]
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
        $url = Config::get('towerify.adversarymeter.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
                'auth' => [
                    config('towerify.adversarymeter.api_username'),
                    config('towerify.adversarymeter.api_password')
                ]
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
