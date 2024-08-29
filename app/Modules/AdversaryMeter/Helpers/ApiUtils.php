<?php

namespace App\Modules\AdversaryMeter\Helpers;

use App\Modules\AdversaryMeter\Exceptions\SentinelApiException;
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

    public static function checkport_public($host, $port, $protocol)
    {
        $cacheKey = 'sentinelapi:checkport:' . $host . ':' . $port . ':' . $protocol;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str(self::checkport_private($host, $port, $protocol)), 6 * 60 * 60 /* 6 hours */);
        }
        return self::json(Psr7\parse_response(Cache::get($cacheKey)));
    }

    private static function checkport_private($host, $port, $protocol)
    {
        $json = [
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol
        ];
        return self::post('/checkport', $json);
    }

    private static function post($endpoint, $json)
    {
        $url = Config::get('towerify.adversarymeter.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
            ]);
            return $client->post($url, self::httpHeaders($json));
        } catch (ClientException $exception) {
            throw new SentinelApiException('Sentinel API Client problem (code: ' . $exception->getCode() . ', url: ' . $url . ')', $exception->getCode(), $exception);
        } catch (Exception $exception) {
            throw new SentinelApiException('Sentinel API problem: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    private static function httpHeaders($json)
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

    private static function json($response, $dataset = null)
    {
        $body = self::body($response);
        return self::isOk($response, $body) ? self::results($response, $body, $dataset) : [];
    }

    public static function body($response)
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

    public static function isOk($response, $body)
    {
        if ($response->getStatusCode() === 200) {
            return true;
        }
        throw new SentinelApiException('Fast API problem: ' . (isset($body['message']) ? $body['message'] : 'unknown'));
    }

    private static function results($response, $body, $dataset = null)
    {
        return self::isOk($response, $body) && !empty($body) ? $body : [];
    }

    public static function discover_public(string $domain)
    {
        $cacheKey = 'sentinelapi:discovery:' . $domain;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str(self::discover_private($domain)), 12 * 60 * 60 /* 12 hours */);
        }
        return self::json(Psr7\parse_response(Cache::get($cacheKey)), $domain);
    }

    private static function discover_private(string $domain)
    {
        $json = array_merge(
            [
                'domain' => $domain,
            ]
        );
        return self::post('/discover', $json);
    }

    public static function screenshot_public(string $domain)
    {
        $response = self::screenshot_private($domain);
        return self::json($response, $domain);
    }

    private static function screenshot_private(string $domain)
    {
        $json = array_merge(
            [
                'url' => $domain,
            ]
        );
        return self::post('/screenshot', $json);
    }

    public static function start_scan_public($asset, $ip, $port, $protocol)
    {
        $response = self::start_scan_private($asset, $ip, $port, $protocol);
        return self::json($response, $asset);
    }

    private static function start_scan_private($asset, $ip, $port, $protocol)
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
        return self::post('/start_scan', $json);
    }

    public static function task_masscan_public(string $host)
    {
        $response = self::task_masscan_private($host);
        return self::json($response, $host);
    }

    private static function task_masscan_private(string $host)
    {
        $json = [
            'input' => [
                $host
            ]
        ];
        return self::post('/task-masscan', $json);
    }

    public static function task_nmap_public(string $host)
    {
        $response = self::task_nmap_private($host);
        return self::json($response, $host);
    }

    private static function task_nmap_private(string $host)
    {
        $json = [
            'input' => [
                $host
            ]
        ];
        return self::post('/task-nmap', $json);
    }

    public static function task_status_public(string $id)
    {
        $response = self::task_status_private($id);
        return self::json($response, $id);
    }

    private static function task_status_private(string $id)
    {
        return self::get('/task_status/' . $id, []);
    }

    private static function get($endpoint, $json)
    {
        $url = Config::get('towerify.adversarymeter.api') . $endpoint;
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => self::REQUEST_TIMEOUT,
            ]);
            return $client->get($url, self::httpHeaders($json));
        } catch (ClientException $exception) {
            throw new SentinelApiException('Sentinel API Client problem (code: ' . $exception->getCode() . ', url: ' . $url . ')', $exception->getCode(), $exception);
        } catch (Exception $exception) {
            throw new SentinelApiException('Sentinel API problem: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public static function task_result_public(string $id)
    {
        $response = self::task_result_private($id);
        return self::json($response, $id);
    }

    private static function task_result_private(string $id)
    {
        return self::get('/task_result/' . $id, []);
    }

    public static function task_start_scan_public($hostname, $ip, $port, $protocol, $tags)
    {
        $response = self::task_start_scan_private($hostname, $ip, $port, $protocol, $tags);
        return self::json($response, $hostname);
    }

    private static function task_start_scan_private($hostName, $ip, $port, $protocol, $tags)
    {
        $json = [
            "hostname" => $hostName,
            "ip" => $ip,
            "port" => $port,
            "protocol" => $protocol,
            "client" => "",
            "tags" => $tags
        ];
        return self::post('/start_scan', $json);
    }

    public static function task_get_scan_public(string $scanId)
    {
        $response = self::task_get_scan_private($scanId);
        return self::json($response, $scanId);
    }

    private static function task_get_scan_private(string $scanId)
    {
        return self::get('/get_scan/' . $scanId, []);
    }

    public static function ip_geoloc_public(string $ip)
    {
        $cacheKey = 'sentinelapi:geoloc:' . $ip;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str(self::ip_geoloc_private($ip)), 72 * 60 * 60 /* 72 hours */);
        }
        return self::json(Psr7\parse_response(Cache::get($cacheKey)), $ip);
    }

    private static function ip_geoloc_private(string $ip)
    {
        $json = [
            'input' => $ip
        ];
        return self::post('/ipgeoloc', $json);
    }

    public static function ip_whois_public(string $ip)
    {
        $cacheKey = 'sentinelapi:whois:' . $ip;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str(self::ip_whois_private($ip)), 72 * 60 * 60 /* 72 hours */);
        }
        return self::json(Psr7\parse_response(Cache::get($cacheKey)), $ip);
    }

    private static function ip_whois_private(string $ip)
    {
        $json = [
            'input' => $ip
        ];
        return self::post('/ipwhois', $json);
    }

    public static function task_discover_full_public(array $urls)
    {
        $response = self::task_discover_full_private($urls);
        return self::json($response);
    }

    private static function task_discover_full_private(array $urls)
    {
        $json = [
            'input' => $urls
        ];
        return self::post('/discover-full-task', $json);
    }

    public static function discover_from_ip_public(string $ip)
    {
        $cacheKey = 'sentinelapi:discovery:' . $ip;
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, Psr7\str(self::discover_from_ip_private($ip)), 12 * 60 * 60 /* 12 hours */);
        }
        return self::json(Psr7\parse_response(Cache::get($cacheKey)), $ip);
    }

    private static function discover_from_ip_private(string $ip)
    {
        $json = [
            'input' => $ip
        ];
        return self::post('/rapid_reverse_ip', $json);
    }

    public static function sentinel_external_ips()
    {
        $response = self::sentinel_external_ips_private();
        return self::json($response);
    }

    public static function sentinel_matched_cves()
    {
        $response = self::sentinel_matched_cves_private();
        return self::json($response);
    }

    private static function sentinel_external_ips_private()
    {
        // ../.. to remove /api/v1/ so go back to root.
        return self::get('/../../sentinel_external_ips', []);
    }

    private static function sentinel_matched_cves_private()
    {
        // ../.. to remove /api/v1/ so go back to root.
        return self::get('/../../cves-list', []);
    }
}
