<?php

namespace App\Listeners;

use App\Events\BeginVulnsScan;
use App\Events\EndPortsScan;
use App\Helpers\VulnerabilityScannerApiUtilsFacade as ApiUtils;
use App\Models\Port;
use App\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EndPortsScanListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::MEDIUM;
    }

    protected function handle2($event)
    {
        if (!($event instanceof EndPortsScan)) {
            throw new \Exception('Invalid event type!');
        }

        $scan = $event->scan();
        $asset = $event->asset();
        $dropEvent = $event->drop();
        $taskResult = $event->taskResult;

        if (!$scan) {
            Log::warning("Ports scan has been removed : {$event->scanId}");
            return;
        }
        if (!$asset) {
            Log::warning("Asset has been removed : {$event->assetId}");
            return;
        }
        if ($scan->portsScanHasEnded()) {
            Log::warning("Ports scan has ended : {$event->scanId}");
            return;
        }
        if (count($taskResult) > 0) {
            $ports = collect($taskResult);
        } else {
            if ($dropEvent) {
                Log::error("Ports scan event is too old : {$event->scanId}");
                $scan->markAsFailed();
                return;
            }
            if (!$scan->portsScanIsRunning()) {
                Log::warning("Ports scan is not running anymore : {$event->scanId}");
                $scan->markAsFailed();
                return;
            }

            $taskId = $scan->ports_scan_id;
            $task = $this->taskStatus($taskId);
            $taskStatus = $task['task_status'] ?? null;

            // The task is running: try again later
            if (!$taskStatus || $taskStatus === 'STARTED' || $taskStatus === 'PENDING') {
                $event->sink();
                return;
            }

            // The task ended with an error
            if ($taskStatus !== 'SUCCESS') {
                Log::error('Ports scan failed : ' . json_encode($task));
                $scan->markAsFailed();
                return;
            }

            $taskOutput = $this->taskOutput($taskId);
            $ports = collect($taskOutput['task_result'] ?? []);
        }
        if ($ports->isEmpty()) {

            // Legacy stuff: if no port is open, create a dummy one that will be marked as closed by the vulns scanner
            $port = Port::create([
                'scan_id' => $scan->id,
                'hostname' => "localhost",
                'ip' => "127.0.0.1",
                'port' => 666,
                'protocol' => "tcp",
            ]);

            $scan->ports_scan_ends_at = Carbon::now();
            $scan->save();

            BeginVulnsScan::dispatch($scan, $port);
        } else {
            $ports->each(function (array $port, int $pos) use ($asset, $scan) {

                $hostname = $port['hostname'] ?? null;
                $ip = $port['ip'] ?? null;
                $portNumber = $port['port'] ?? null;
                $protocol = $port['protocol'] ?? null;

                try {
                    $geoloc = $this->ipGeoLoc($ip);
                    $country = $geoloc['data']['country']['iso_code'] ?? null;
                } catch (\Exception $e) {
                    $country = null;
                }
                try {
                    $hostingProvider = $this->hostingProvider($ip);
                    $description = $hostingProvider['data']['asn_description'] ?? null;
                    $registry = $hostingProvider['data']['asn_registry'] ?? null;
                    $asn = $hostingProvider['data']['asn'] ?? null;
                    $cidr = $hostingProvider['data']['asn_cidr'] ?? null;
                    $countryCode = $hostingProvider['data']['asn_country_code'] ?? null;
                    $date = $hostingProvider['data']['asn_date'] ?? null;
                } catch (\Exception $e) {
                    $description = null;
                    $registry = null;
                    $asn = null;
                    $cidr = null;
                    $countryCode = null;
                    $date = null;
                }
                if ($pos === 0) {
                    $scan->ports_scan_ends_at = Carbon::now();
                    $scan->save();
                    $newScan = $scan;
                } else {
                    $newScan = Scan::create([
                        'asset_id' => $scan->asset_id,
                        'ports_scan_id' => $scan->ports_scan_id,
                        'ports_scan_begins_at' => $scan->ports_scan_begins_at,
                        'ports_scan_ends_at' => $scan->ports_scan_ends_at,
                    ]);
                }

                $newPort = Port::create([
                    'scan_id' => $newScan->id,
                    'hostname' => trim($hostname),
                    'ip' => trim($ip),
                    'port' => $portNumber,
                    'protocol' => trim($protocol),
                    'country' => $country,
                    'hosting_service_description' => $description,
                    'hosting_service_registry' => $registry,
                    'hosting_service_asn' => $asn,
                    'hosting_service_cidr' => $cidr,
                    'hosting_service_country_code' => $countryCode,
                    'hosting_service_date' => $date,
                ]);

                BeginVulnsScan::dispatch($newScan, $newPort);
            });
        }
    }

    private function taskStatus(string $taskId): array
    {
        return ApiUtils::task_status_public($taskId);
    }

    private function taskOutput(string $taskId): array
    {
        return ApiUtils::task_result_public($taskId);
    }

    private function ipGeoLoc(string $ip): array
    {
        return ApiUtils::ip_geoloc_public($ip);
    }

    private function hostingProvider(string $ip): array
    {
        return ApiUtils::ip_whois_public($ip);
    }
}
