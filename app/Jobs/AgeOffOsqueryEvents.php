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

        // Deduplicate events
        $min = Carbon::today()->subHours(1);
        DB::delete("
            DELETE t1 FROM ynh_osquery t1
            INNER JOIN ynh_osquery t2
            WHERE t1.id < t2.id
            AND t1.calendar_time >= '{$min->toDateString()}'
            AND t1.ynh_server_id = t2.ynh_server_id
            AND t1.name = t2.name
            AND t1.host_identifier = t2.host_identifier
            AND t1.calendar_time = t2.calendar_time
            AND t1.unix_time = t2.unix_time
            AND t1.epoch = t2.epoch
            AND t1.counter = t2.counter
            AND t1.numerics = t2.numerics
            AND t1.columns = t2.columns
            AND t1.action = t2.action;
        ");
    }
}
