<?php

namespace App\Listeners;

use App\Events\BeginDiscovery;
use App\Events\EndDiscovery;
use App\Helpers\VulnerabilityScannerApiUtilsFacade as ApiUtils;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BeginDiscoveryListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::LOW;
    }

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
            Log::error('Assets discovery cannot be started : ' . json_encode($task));
        } else {
            Asset::where('tld', $tld)->update(['discovery_id', $taskId]);
            EndDiscovery::dispatch(Carbon::now(), $tld, $taskId);
        }
    }

    private function beginTask(string $tld): array
    {
        return ApiUtils::task_discover_full_public([$tld]);
    }
}
