<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
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

        /** @var string $tld */
        $tld = $event->tld;
        /** @var string $taskId */
        $taskId = $event->taskId;
        $task = $this->taskStatus($taskId);
        $taskStatus = $task['task_status'] ?? null;

        // The task is running: try again later
        if (!$taskStatus) {
            event(new EndDiscovery($tld, $taskId));
            return;
        }

        // The task ended with an error
        if ($taskStatus !== 'SUCCESS') {
            if ($taskStatus === 'FAILURE') {
                Log::error('Assets discovery failed: ' . json_encode($task));
                Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
                return;
            }
            event(new EndDiscovery($tld, $taskId));
            return;
        }

        $taskOutput = $this->taskOutput($taskId);

        if (!isset($taskOutput['task_result'])) {
            Log::error('Assets discovery failed: ' . json_encode($taskOutput));
            Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
            return;
        }

        $assets = json_decode($taskOutput['task_result'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Assets discovery failed: ' . json_encode($taskOutput));
            Asset::where('discovery_id', $taskId)->update(['discovery_id', null]);
            return;
        }
        foreach ($assets as $asset) {
            Asset::create([ // TODO : backport confidence score $asset['score'] / 100
                'asset' => $asset['domain'],
                'asset_type' => AssetTypesEnum::DNS,
                'tld' => $tld,
            ]);
        }

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
