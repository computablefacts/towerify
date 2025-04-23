<?php

namespace App\Jobs;

use App\Events\BeginPortsScan;
use App\Models\Asset;
use App\Models\Scan;
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

        // Remove running scans that are not in the 'prev', 'cur' or 'next' status
        Scan::removeDanglingScans();

        // Begin a new scan
        Asset::whereNull('next_scan_id')
            ->where('is_monitored', true)
            ->get()
            ->filter(function (Asset $asset) use ($minDate) {
                $scans = $asset->scanCompleted();
                return $scans->isEmpty() || $scans->sortBy('vulns_scan_ends_at')->last()?->vulns_scan_ends_at <= $minDate;
            })
            ->each(fn(Asset $asset) => BeginPortsScan::dispatch($asset));
    }
}
