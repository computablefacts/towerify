<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\EndVulnsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EndVulnsScanListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof EndVulnsScan)) {
            throw new \Exception('Invalid event type!');
        }

        $scan = $event->scan();
        $dropEvent = $event->drop();

        if (!$scan) {
            Log::warning("Vulns scan has been removed : {$event->scanId}");
            return;
        }
        if ($dropEvent) {
            Log::error("Vulns scan event is too old : {$event->scanId}");
            $scan->markAsFailed();
            return;
        }
        if (!$scan->vulnsScanIsRunning()) {
            Log::warning("Vulns scan is not running anymore : {$event->scanId}");
            $scan->markAsFailed();
            return;
        }

        $taskId = $scan->vulns_scan_id;
        $task = $this->taskOutput($taskId);
        $currentTaskName = $task['current_task'] ?? null;
        $currentTaskStatus = $task['current_task_status'] ?? null;

        if ($currentTaskName !== 'alerter' || $currentTaskStatus !== 'DONE') {

            $isCsc = isset($task['tags']) && collect($task['tags'])->filter(fn(string $tag) => Str::startsWith($tag, 'Csc_'))->isNotEmpty();

            if (!$isCsc) {

                if ($currentTaskStatus && Str::startsWith($currentTaskStatus, 'DONE_')) {
                    $event->sink();
                    return;
                }

                $service = $task['service'] ?? null;

                if ($service !== 'closed') {
                    $event->sink();
                } else { // End the scan!
                    $this->markAssetScanAsCompleted($scan);
                }
                return;
            }
        }

        $service = $task['service'] ?? null;
        $product = $task['product'] ?? null;
        $ssl = $task['ssl'] ?? null;

        $port = Port::where('scan_id', $scan->id)->first();
        $port->service = $service;
        $port->product = $product;
        $port->ssl = $ssl ? 1 : 0;
        $port->save();

        $tags = collect($task['tags'] ?? []);
        $tags->each(function (string $label) use ($port) {
            $port->tags()->create(['tag' => Str::lower($label)]);
        });

        $this->setAlertsV1($port, $task);
        $this->setAlertsV2($port, $task);

        // TODO : deal with screenshot

        $this->markAssetScanAsCompleted($scan);
    }

    private function setAlertsV1(Port $port, array $task): void
    {
        collect($task['data'] ?? [])
            ->filter(fn(array $data) => $data['tool'] === 'alerter' && $data['rawOutput'])
            ->flatMap(fn(array $data) => collect(preg_split('/\r\n|\r|\n/', $data['rawOutput'])))
            ->filter(fn(string $alert) => $alert !== '')
            ->map(fn(string $alert) => json_decode($alert, true))
            ->each(function (array $alert) use ($port) {
                try {
                    Alert::updateOrCreate([
                        'port_id' => $port->id,
                        'uid' => trim($alert['values'][7])
                    ], [
                        'port_id' => $port->id,
                        'type' => trim($alert['type']),
                        'vulnerability' => trim($alert['values'][4]),
                        'remediation' => trim($alert['values'][5]),
                        'level' => trim($alert['values'][6]),
                        'uid' => trim($alert['values'][7]),
                        'cve_id' => empty($alert['values'][8]) ? null : $alert['values'][8],
                        'cve_cvss' => empty($alert['values'][9]) ? null : $alert['values'][9],
                        'cve_vendor' => empty($alert['values'][10]) ? null : $alert['values'][10],
                        'cve_product' => empty($alert['values'][11]) ? null : $alert['values'][11],
                        'title' => trim($alert['values'][12]),
                        'flarum_slug' => null, // TODO : remove?
                    ]);
                } catch (\Exception $exception) {
                    Log::error($exception);
                    Log::error($alert);
                }
            });
    }

    private function setAlertsV2(Port $port, array $task): void
    {
        collect($task['data'] ?? [])
            ->filter(fn(array $data) => isset($data['alerts']) && count($data['alerts']))
            ->flatMap(fn(array $data) => collect($data['alerts'] ?? []))
            ->filter(fn($alert) => $alert !== '')
            ->each(function (array $alert) use ($port) {
                try {
                    $type = trim($alert['type']);

                    if (!str_ends_with($type, '_alert')) {
                        $type .= '_v3_alert';
                    }

                    Alert::updateOrCreate([
                        'port_id' => $port->id,
                        'uid' => trim($alert['uid'])
                    ], [
                        'port_id' => $port->id,
                        'type' => $type,
                        'vulnerability' => trim($alert['vulnerability']),
                        'remediation' => trim($alert['remediation']),
                        'level' => trim($alert['level']),
                        'uid' => trim($alert['uid']),
                        'cve_id' => empty($alert['cve_id']) ? null : $alert['cve_id'],
                        'cve_cvss' => empty($alert['cve_cvss']) ? null : $alert['cve_cvss'],
                        'cve_vendor' => empty($alert['cve_vendor']) ? null : $alert['cve_vendor'],
                        'cve_product' => empty($alert['cve_product']) ? null : $alert['cve_product'],
                        'title' => trim($alert['title']),
                        'flarum_slug' => null, // TODO : remove?
                    ]);
                } catch (\Exception $exception) {
                    Log::error($exception);
                    Log::error($alert);
                }
            });
    }

    private function markAssetScanAsCompleted(Scan $scan): void
    {
        DB::transaction(function () use ($scan) {

            $scan->vulns_scan_ends_at = Carbon::now();
            $scan->save();

            $remaining = Scan::where('asset_id', $scan->asset_id)
                ->where('ports_scan_id', $scan->ports_scan_id)
                ->whereNull('vulns_scan_ends_at')
                ->count();

            if ($remaining === 0) {

                $asset = Asset::where('id', $scan->asset_id)->first();

                if ($asset) {
                    if ($asset->cur_scan_id === $scan->ports_scan_id) {
                        return; // late arrival, ex. when events are processed synchronously
                    }
                    if ($asset->prev_scan_id) {
                        Scan::where('asset_id', $scan->asset_id)
                            ->where('id', $asset->prev_scan_id)
                            ->delete();
                    }

                    $asset->prev_scan_id = $asset->cur_scan_id;
                    $asset->cur_scan_id = $asset->next_scan_id;
                    $asset->next_scan_id = null;
                    $asset->save();
                }
            }
        });
    }

    private function taskOutput(string $taskId): array
    {
        return ApiUtils::task_get_scan_public($taskId);
    }
}