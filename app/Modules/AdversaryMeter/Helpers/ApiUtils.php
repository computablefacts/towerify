<?php

namespace App\Modules\AdversaryMeter\Helpers;

use App\Modules\AdversaryMeter\Exceptions\ApiException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use function GuzzleHttp\Psr7;

class ApiUtils
{
    const REQUEST_TIMEOUT = 300; // 5 minutes

    public function checkport_public($host, $port, $protocol)
    {
        $cacheKey = 'am_api:checkport:' . $host . ':' . $port . ':' . $protocol;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str($this->checkport_private($host, $port, $protocol)), 6 * 60 * 60 /* 6 hours */);
        }
        return $this->json(Psr7\parse_response(Cache::get($cacheKey)));
    }

    private function checkport_private($host, $port, $protocol)
    {
        $json = [
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol
        ];
        return $this->post('/checkport', $json);
    }

    private function post($endpoint, $json)
    {
        $url = Config::get('towerify.adversarymeter.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => $this->REQUEST_TIMEOUT,
            ]);
            return $client->post($url, $this->httpHeaders($json));
        } catch (ClientException $exception) {
            throw new ApiException('API client problem (code: ' . $exception->getCode() . ', url: ' . $url . ')', $exception->getCode(), $exception);
        } catch (Exception $exception) {
            throw new ApiException('API problem: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private function httpHeaders($json)
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

    private function json($response, $dataset = null)
    {
        $body = $this->body($response);
        return $this->isOk($response, $body) ? $this->results($response, $body, $dataset) : [];
    }

    public function body($response)
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

    public function isOk($response, $body)
    {
        if ($response->getStatusCode() === 200) {
            return true;
        }
        throw new ApiException('Fast API problem: ' . (isset($body['message']) ? $body['message'] : 'unknown'));
    }

    private function results($response, $body, $dataset = null)
    {
        return $this->isOk($response, $body) && !empty($body) ? $body : [];
    }

    public function discover_public(string $domain)
    {
        $cacheKey = 'am_api:discovery:' . $domain;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str($this->discover_private($domain)), 12 * 60 * 60 /* 12 hours */);
        }
        return $this->json(Psr7\parse_response(Cache::get($cacheKey)), $domain);
    }

    private function discover_private(string $domain)
    {
        $json = array_merge(
            [
                'domain' => $domain,
            ]
        );
        return $this->post('/discover', $json);
    }

    public function screenshot_public(string $domain)
    {
        $response = $this->screenshot_private($domain);
        return $this->json($response, $domain);
    }

    private function screenshot_private(string $domain)
    {
        $json = array_merge(
            [
                'url' => $domain,
            ]
        );
        return $this->post('/screenshot', $json);
    }

    public function start_scan_public($asset, $ip, $port, $protocol)
    {
        $response = $this->start_scan_private($asset, $ip, $port, $protocol);
        return $this->json($response, $asset);
    }

    private function start_scan_private($asset, $ip, $port, $protocol)
    {
        $json = array_merge(
            [
                'hostname' => $asset,
                'ip' => $ip,
                'port' => $port,
                'protocol' => $protocol,
                'client' => null,
                'tags' => [],
                'tests' => [],
            ]
        );
        return $this->post('/start_scan', $json);
    }

    public function task_masscan_public(string $host)
    {
        $response = $this->task_masscan_private($host);
        return $this->json($response, $host);
    }

    private function task_masscan_private(string $host)
    {
        $json = [
            'input' => [
                $host
            ]
        ];
        return $this->post('/task-masscan', $json);
    }

    public function task_nmap_public(string $host)
    {
        $response = $this->task_nmap_private($host);
        return $this->json($response, $host);
    }

    private function task_nmap_private(string $host)
    {
        $json = [
            'input' => [
                $host
            ]
        ];
        return $this->post('/task-nmap', $json);
    }

    public function task_status_public(string $id)
    {
        $response = $this->task_status_private($id);
        return $this->json($response, $id);
    }

    private function task_status_private(string $id)
    {
        return $this->get('/task_status/' . $id, []);
    }

    private function get($endpoint, $json)
    {
        $url = Config::get('towerify.adversarymeter.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => $this->REQUEST_TIMEOUT,
            ]);
            return $client->get($url, $this->httpHeaders($json));
        } catch (ClientException $exception) {
            throw new ApiException('API client problem (code: ' . $exception->getCode() . ', url: ' . $url . ')', $exception->getCode(), $exception);
        } catch (Exception $exception) {
            throw new ApiException('API problem: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function task_result_public(string $id)
    {
        $response = $this->task_result_private($id);
        return $this->json($response, $id);
    }

    private function task_result_private(string $id)
    {
        return $this->get('/task_result/' . $id, []);
    }

    public function task_start_scan_public($hostname, $ip, $port, $protocol, $tags)
    {
        $response = $this->task_start_scan_private($hostname, $ip, $port, $protocol, $tags);
        return $this->json($response, $hostname);
    }

    private function task_start_scan_private($hostName, $ip, $port, $protocol, $tags)
    {
        $json = [
            "hostname" => $hostName,
            "ip" => $ip,
            "port" => $port,
            "protocol" => $protocol,
            "client" => "",
            "tags" => $tags
        ];
        return $this->post('/start_scan', $json);
    }

    public function task_get_scan_public(string $scanId)
    {
        $response = $this->task_get_scan_private($scanId);
        return $this->json($response, $scanId);
    }

    private function task_get_scan_private(string $scanId)
    {
        return $this->get('/get_scan/' . $scanId, []);
    }

    public function ip_geoloc_public(string $ip)
    {
        $cacheKey = 'am_api:geoloc:' . $ip;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str($this->ip_geoloc_private($ip)), 72 * 60 * 60 /* 72 hours */);
        }
        return $this->json(Psr7\parse_response(Cache::get($cacheKey)), $ip);
    }

    private function ip_geoloc_private(string $ip)
    {
        $json = [
            'input' => $ip
        ];
        return $this->post('/ipgeoloc', $json);
    }

    public function ip_whois_public(string $ip)
    {
        $cacheKey = 'am_api:whois:' . $ip;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str($this->ip_whois_private($ip)), 72 * 60 * 60 /* 72 hours */);
        }
        return $this->json(Psr7\parse_response(Cache::get($cacheKey)), $ip);
    }

    private function ip_whois_private(string $ip)
    {
        $json = [
            'input' => $ip
        ];
        return $this->post('/ipwhois', $json);
    }

    public function task_discover_full_public(array $urls)
    {
        $response = $this->task_discover_full_private($urls);
        return $this->json($response);
    }

    private function task_discover_full_private(array $urls)
    {
        $json = [
            'input' => $urls
        ];
        return $this->post('/discover-full-task', $json);
    }

    public function discover_from_ip_public(string $ip)
    {
        $cacheKey = 'am_api:discovery:' . $ip;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str($this->discover_from_ip_private($ip)), 12 * 60 * 60 /* 12 hours */);
        }
        return $this->json(Psr7\parse_response(Cache::get($cacheKey)), $ip);
    }

    private function discover_from_ip_private(string $ip)
    {
        $json = [
            'input' => $ip
        ];
        return $this->post('/rapid_reverse_ip', $json);
    }

    public function external_ips()
    {
        $response = $this->external_ips_private();
        return $this->json($response);
    }

    public function matched_cves()
    {
        $response = $this->matched_cves_private();
        return $this->json($response);
    }

    private function external_ips_private()
    {
        // ../.. to remove /api/v1/ so go back to root.
        return $this->get('/../../sentinel_external_ips', []);
    }

    private function matched_cves_private()
    {
        // ../.. to remove /api/v1/ so go back to root.
        return $this->get('/../../cves-list', []);
    }
}
