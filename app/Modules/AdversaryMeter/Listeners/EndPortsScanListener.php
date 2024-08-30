<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\BeginVulnsScan;
use App\Modules\AdversaryMeter\Events\EndPortsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EndPortsScanListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof EndPortsScan)) {
            throw new \Exception('Invalid event type!');
        }

        $asset = $event->asset();
        $scan = $event->scan();

        if (!$scan->portsScanIsRunning()) {
            return;
        }

        $taskId = $scan->ports_scan_id;
        $task = $this->taskStatus($taskId);
        $taskStatus = $task['task_status'] ?? null;

        // The task is running: try again later
        if (!$taskStatus) {
            event(new EndPortsScan($asset, $scan));
            return;
        }

        // The task ended with an error
        if ($taskStatus !== 'SUCCESS') {
            Log::error('Ports scan failed: ' . json_encode($task));
            $scan->markAssetScanAsFailed();
            return;
        }

        $taskOutput = $this->taskOutput($taskId);
        $ports = collect($taskOutput['task_result'] ?? []);
        $ports->each(function (array $port, int $pos) use ($asset, $scan) {

            $hostname = $port['hostname'] ?? null;
            $ip = $port['ip'] ?? null;
            $portNumber = $port['port'] ?? null;
            $protocol = $port['protocol'] ?? null;

            $geoloc = $this->ipGeoLoc($ip);
            $country = $geoloc['data']['country']['iso_code'] ?? null;

            $hostingProvider = $this->hostingProvider($ip);
            $description = $hostingProvider['data']['asn_description'] ?? null;
            $registry = $hostingProvider['data']['asn_registry'] ?? null;
            $asn = $hostingProvider['data']['asn'] ?? null;
            $cidr = $hostingProvider['data']['asn_cidr'] ?? null;
            $countryCode = $hostingProvider['data']['asn_country_code'] ?? null;
            $date = $hostingProvider['data']['asn_date'] ?? null;

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
                'country' => trim($country),
                'hosting_service_description' => trim($description),
                'hosting_service_registry' => trim($registry),
                'hosting_service_asn' => trim($asn),
                'hosting_service_cidr' => trim($cidr),
                'hosting_service_country_code' => trim($countryCode),
                'hosting_service_date' => trim($date),
            ]);

            event(new BeginVulnsScan($newScan, $newPort));
        });
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
