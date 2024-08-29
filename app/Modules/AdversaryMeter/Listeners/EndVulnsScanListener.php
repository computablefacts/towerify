<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\EndVulnsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtils;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\PortTag;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EndVulnsScanListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof EndVulnsScan)) {
            throw new \Exception('Invalid event type!');
        }

        /** @var Scan $scan */
        $scan = $event->scan;

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
                    event(new EndVulnsScan($scan));
                    return;
                }

                $service = $task['service'] ?? null;

                if ($service !== 'closed') {
                    event(new EndVulnsScan($scan));
                } else { // End the scan!
                    $scan->vulns_scan_ends_at = Carbon::now();
                    $scan->save();
                    $scan->markAssetScanAsCompleted();
                }
                return;
            }
        }

        $service = $task['service'] ?? null;
        $product = $task['product'] ?? null;
        $ssl = isset($task['ssl']) ? var_export($task['ssl'], true) : false;

        $port = Port::where('scan_id')->first();
        $port->service = $service;
        $port->product = $product;
        $port->ssl = $ssl;
        $port->save();

        $tags = collect($task['tags'] ?? []);
        $tags->each(function (string $label) use ($port) {
            $tag = PortTag::create([
                'port_id' => $port->id,
                'tag' => Str::lower($label),
            ]);
            $port->tags()->attach($tag);
        });

        $this->setAlertsV1($port, $task);
        $this->setAlertsV2($port, $task);

        // TODO : deal with screenshot

        $scan->vulns_scan_ends_at = Carbon::now();
        $scan->save();
        $scan->markAssetScanAsCompleted();
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

    private function taskOutput(string $taskId): array
    {
        return ApiUtils::task_get_scan_public($taskId);
    }
}
