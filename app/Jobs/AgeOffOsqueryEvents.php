<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AgeOffOsqueryEvents implements ShouldQueue
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
        // Age-off MEMORY/DISK data points
        $min = Carbon::today()->subDays(5);
        DB::delete("
            DELETE FROM ynh_osquery
            WHERE calendar_time <= '{$min->toDateString()}'
            AND name IN ('memory_available_snapshot', 'disk_available_snapshot')
        ");
    }
}
