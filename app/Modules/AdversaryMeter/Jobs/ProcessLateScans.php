<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Events\EndPortsScan;
use App\Modules\AdversaryMeter\Events\EndVulnsScan;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLateScans implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function handle()
    {
        $dropAfter = config('towerify.adversarymeter.drop_scan_events_after_x_minutes');
        Asset::whereNotNull('next_scan_id')
            ->where('is_monitored', true)
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->scanInProgress())
            ->each(function (Scan $scan) use ($dropAfter) {
                if ($scan->portsScanIsRunning()) {
                    $droppedAt = $scan->ports_scan_begins_at->addMinutes($dropAfter);
                    if ($droppedAt < Carbon::now()) {
                        event(new EndPortsScan(Carbon::now(), $scan->asset()->first(), $scan));
                    }
                } elseif ($scan->vulnsScanIsRunning()) {
                    $droppedAt = $scan->vulns_scan_begins_at->addMinutes($dropAfter);
                    if ($droppedAt < Carbon::now()) {
                        event(new EndVulnsScan(Carbon::now(), $scan));
                    }
                }
            });
    }
}
