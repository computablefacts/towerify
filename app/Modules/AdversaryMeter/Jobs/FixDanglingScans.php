<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Events\EndPortsScan;
use App\Modules\AdversaryMeter\Events\EndVulnsScan;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** @deprecated */
class FixDanglingScans implements ShouldQueue
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
        Scan::query()
            ->whereNotNull('asset_id')
            ->whereNotNull('ports_scan_id')
            ->whereNotNull('ports_scan_begins_at')
            ->whereNull('ports_scan_ends_at')
            ->whereNull('vulns_scan_id')
            ->whereNull('vulns_scan_begins_at')
            ->whereNull('vulns_scan_ends_at')
            ->get()
            ->each(function (Scan $scan) {
                if (Carbon::now()->diffInHours($scan->ports_scan_begins_at, true) > 6) {
                    EndPortsScan::dispatch($scan->ports_scan_begins_at, $scan->asset()->first(), $scan);
                }
            });
        Scan::query()
            ->whereNotNull('asset_id')
            ->whereNotNull('ports_scan_id')
            ->whereNotNull('ports_scan_begins_at')
            ->whereNotNull('ports_scan_ends_at')
            ->whereNotNull('vulns_scan_id')
            ->whereNotNull('vulns_scan_begins_at')
            ->whereNull('vulns_scan_ends_at')
            ->get()
            ->each(function (Scan $scan) {
                if (Carbon::now()->diffInHours($scan->vulns_scan_begins_at, true) > 6) {
                    EndVulnsScan::dispatch($scan->vulns_scan_begins_at, $scan);
                }
            });
    }
}
