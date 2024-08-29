<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\BeginPortsScan;
use App\Modules\AdversaryMeter\Events\EndPortsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
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

        /** @var Asset $asset */
        $asset = $event->asset;
        $task = $this->beginTask($asset);
        $taskId = $task['task_id'] ?? null;

        if (!$taskId) {
            Log::error('Ports scan cannot be started: ' . json_encode($task));
        } else {

            $scan = Scan::create([
                'ports_scan_id' => $taskId,
                'ports_scan_begins_at' => Carbon::now(),
            ]);
            $asset->nextScan()->attach($scan);

            event(new EndPortsScan($scan));
        }
    }

    private function beginTask(string $asset): array
    {
        return ApiUtils::task_nmap_public($asset);
    }
}
