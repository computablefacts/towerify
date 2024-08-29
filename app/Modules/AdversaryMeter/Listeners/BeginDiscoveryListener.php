<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\BeginDiscovery;
use App\Modules\AdversaryMeter\Events\EndDiscovery;
use App\Modules\AdversaryMeter\Helpers\ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Support\Facades\Log;

class BeginDiscoveryListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof BeginDiscovery)) {
            throw new \Exception('Invalid event type!');
        }

        /** @var string $tld */
        $tld = $event->tld;
        $task = $this->beginTask($tld);
        $taskId = $task['task_id'] ?? null;

        if (!$taskId) {
            Log::error('Assets discovery cannot be started: ' . json_encode($task));
        } else {
            Asset::where('tld', $tld)->update(['discovery_id', $taskId]);
            event(new EndDiscovery($tld, $taskId));
        }
    }

    private function beginTask(string $tld): array
    {
        return ApiUtils::task_discover_full_public([$tld]);
    }
}
