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
use Illuminate\Support\Str;

class EndVulnsScanListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof EndVulnsScan)) {
            throw new \Exception('Invalid event type!');
        }

        $scan = $event->scan();
        $iteration = $event->iteration;

        if (!$scan->vulnsScanIsRunning()) {
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
                    event(new EndVulnsScan($scan, $iteration + 1));
                    return;
                }

                $service = $task['service'] ?? null;

                if ($service !== 'closed') {
                    if ($iteration < 100) {
                        event(new EndVulnsScan($scan, $iteration + 1));
                    } else { // Drop event after 100 iterations
                        $scan->markAssetScanAsFailed();
                    }
                } else { // End the scan!
                    $this->markAssetScanAsCompleted($scan);
                }
                return;
            }
        }

        $service = $task['service'] ?? null;
        $product = $task['product'] ?? null;
        $ssl = isset($task['ssl']) ? var_export($task['ssl'], true) : false;

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
        $tool = $task['tool'] ?? null;
        $output = $task['rawOutput'] ?? null;

        if ($tool !== 'alerter' || !$output) {
            return;
        }

        $alerts = preg_split('/\r\n|\r|\n/', $output);
        collect($alerts)
            ->filter(fn(string $alert) => $alert !== '')
            ->map(fn(string $alert) => json_decode($alert, true))
            ->each(function (array $alert) use ($port) {
                Alert::create([
                    'port_id' => $port->id,
                    'type' => trim($alert['type']),
                    'vulnerability' => trim($alert['values'][4]),
                    'remediation' => trim($alert['values'][5]),
                    'level' => trim($alert['values'][6]),
                    'uid' => trim($alert['values'][7]),
                    'cve_id' => trim($alert['values'][8]),
                    'cve_cvss' => trim($alert['values'][9]),
                    'cve_vendor' => trim($alert['values'][10]),
                    'cve_product' => trim($alert['values'][11]),
                    'title' => trim($alert['values'][12]),
                    'flarum_slug' => null, // TODO : remove?
                ]);
            });
    }

    private function setAlertsV2(Port $port, array $task): void
    {
        collect($task['alerts'] ?? [])
            ->filter(fn(array|string $alert) => $alert !== '')
            ->each(function (array $alert) use ($port) {

                $type = trim($alert['type']);

                if (!str_ends_with($type, '_alert')) {
                    $type .= '_v3_alert';
                }

                Alert::create([
                    'port_id' => $port->id,
                    'type' => $type,
                    'vulnerability' => trim($alert['vulnerability']),
                    'remediation' => trim($alert['remediation']),
                    'level' => trim($alert['level']),
                    'uid' => trim($alert['uid']),
                    'cve_id' => trim($alert['cve_id']),
                    'cve_cvss' => trim($alert['cve_cvss']),
                    'cve_vendor' => trim($alert['cve_vendor']),
                    'cve_product' => trim($alert['cve_product']),
                    'title' => trim($alert['title']),
                    'flarum_slug' => null, // TODO : remove?
                ]);
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
