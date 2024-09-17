<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Events\EndDiscovery;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Support\Facades\Log;

class EndDiscoveryListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof EndDiscovery)) {
            throw new \Exception('Invalid event type!');
        }

        $tld = $event->tld;
        $taskId = $event->taskId;
        $dropEvent = $event->drop();

        if ($dropEvent) {
            Log::error("Discovery event is too old : {$tld}");
            Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
            return;
        }

        $task = $this->taskStatus($taskId);
        $taskStatus = $task['task_status'] ?? null;

        // The task is running: try again later
        if (!$taskStatus) {
            $event->sink();
            return;
        }

        // The task ended with an error
        if ($taskStatus !== 'SUCCESS') {
            if ($taskStatus === 'FAILURE') {
                Log::error('Assets discovery failed : ' . json_encode($task));
                Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
                return;
            }
            $event->sink();
            return;
        }

        $taskOutput = $this->taskOutput($taskId);

        if (!isset($taskOutput['task_result'])) {
            Log::error('Assets discovery failed : ' . json_encode($taskOutput));
            Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
            return;
        }

        $assets = json_decode($taskOutput['task_result'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Assets discovery failed : ' . json_encode($taskOutput));
            Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
            return;
        }

        collect($assets)
            ->map(fn(array $asset) => $asset['domain'])
            ->filter(fn(string $domain) => !empty($domain))
            ->each(function (string $domain) use ($tld, $taskId) {
                // TODO : backport confidence score $asset['score'] / 100
                Asset::where('tld', $tld)
                    ->get()
                    ->each(function (Asset $asset) use ($domain) {
                        event(new CreateAsset($asset->createdBy(), $domain, true));
                    });
            });

        Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
    }

    private function taskStatus(string $taskId): array
    {
        return ApiUtils::task_status_public($taskId);
    }

    private function taskOutput(string $taskId): array
    {
        return ApiUtils::task_result_public($taskId);
    }
}
