<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Events\BeginPortsScan;
use App\Modules\AdversaryMeter\Models\Asset;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TriggerScan implements ShouldQueue
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
        $frequency = config('towerify.adversarymeter.days_between_scans');
        $minDate = Carbon::now()->subDays((int)$frequency);
        Asset::whereNull('next_scan_id')
            ->where('is_monitored', true)
            ->get()
            ->filter(function (Asset $asset) use ($minDate) {
                $scan = $asset->scanCompleted();
                return !$scan || $scan->vulns_scan_ends_at <= $minDate;
            })
            ->each(fn(Asset $asset) => event(new BeginPortsScan($asset)));
    }
}
