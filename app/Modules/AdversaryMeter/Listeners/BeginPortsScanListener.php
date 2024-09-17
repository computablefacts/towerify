<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\BeginPortsScan;
use App\Modules\AdversaryMeter\Events\EndPortsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BeginPortsScanListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof BeginPortsScan)) {
            throw new \Exception('Invalid event type!');
        }

        $asset = $event->asset();

        if (!$asset) {
            Log::warning("Asset has been removed : {$event->assetId}");
            return;
        }

        $task = $this->beginTask($asset->asset);
        $taskId = $task['task_id'] ?? null;

        if (!$taskId) {
            Log::error('Ports scan cannot be started : ' . json_encode($task));
        } else {

            $scan = Scan::create([
                'asset_id' => $asset->id,
                'ports_scan_id' => $taskId,
                'ports_scan_begins_at' => Carbon::now(),
            ]);

            $asset->next_scan_id = $taskId;
            $asset->save();

            event(new EndPortsScan(Carbon::now(), $asset, $scan));
        }
    }

    private function beginTask(string $asset): array
    {
        return ApiUtils::task_nmap_public($asset);
    }
}
