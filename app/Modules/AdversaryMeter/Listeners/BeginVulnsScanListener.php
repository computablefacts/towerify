<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\BeginVulnsScan;
use App\Modules\AdversaryMeter\Events\EndVulnsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BeginVulnsScanListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof BeginVulnsScan)) {
            throw new \Exception('Invalid event type!');
        }

        $scan = $event->scan();
        $port = $event->port();

        if (!$scan->portsScanHasEnded()) {
            return;
        }

        $tags = Asset::where('id', $scan->asset_id)
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->tags()->get())
            ->map(fn(AssetTag $tag) => $tag->tag)
            ->toArray();
        $task = $this->beginTask($port->hostname, $port->ip, $port->port, $port->protocol, $tags);
        $taskId = $task['scan_id'] ?? null;

        if (!$taskId) {
            Log::error('Vulns scan cannot be started: ' . json_encode($task));
            $scan->markAssetScanAsFailed();
        } else {

            $scan->vulns_scan_id = $taskId;
            $scan->vulns_scan_begins_at = Carbon::now();
            $scan->save();

            event(new EndVulnsScan($scan));
        }
    }

    private function beginTask(string $hostname, string $ip, int $port, string $protocol, array $tags): array
    {
        return ApiUtils::task_start_scan_public($hostname, $ip, $port, $protocol, $tags);
    }
}
