<?php

namespace App\Jobs;

use App\Models\YnhOsquery;
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
        $this->historizeDiskUsage();
        $this->historizeMemoryUsage();
    }

    private function historizeDiskUsage(): void
    {
        DB::transaction(function () {

            YnhOsquery::where('name', 'disk_available_snapshot')
                ->where('packed', true)
                ->update(['packed' => false]);

            DB::unprepared("
                INSERT INTO ynh_disk_usage (
                  ynh_server_id,
                  timestamp,
                  percent_available,
                  percent_used,
                  space_left_gb,
                  total_space_gb,
                  used_space_gb
                )
                SELECT 
                    ynh_osquery.ynh_server_id,
                    TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS `timestamp`,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_available'))), 2) AS percent_available,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_used'))), 2) AS percent_used,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.space_left_gb'))), 2) AS space_left_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.total_space_gb'))), 2) AS total_space_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.used_space_gb'))), 2) AS used_space_gb
                FROM ynh_osquery
                WHERE ynh_osquery.name = 'disk_available_snapshot'
                AND ynh_osquery.packed = 0
                GROUP BY ynh_osquery.ynh_server_id, ynh_osquery.calendar_time
            ");

            YnhOsquery::where('name', 'disk_available_snapshot')
                ->where('packed', false)
                ->delete();
        });
    }

    private function historizeMemoryUsage(): void
    {
        DB::transaction(function () {

            YnhOsquery::where('name', 'memory_available_snapshot')
                ->where('packed', true)
                ->update(['packed' => false]);

            DB::unprepared("
                INSERT INTO ynh_memory_usage (
                  ynh_server_id,
                  timestamp,
                  percent_available,
                  percent_used,
                  space_left_gb,
                  total_space_gb,
                  used_space_gb
                )
                SELECT 
                    ynh_osquery.ynh_server_id,
                    TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS `timestamp`,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_available'))), 2) AS percent_available,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_used'))), 2) AS percent_used,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.space_left_gb'))), 2) AS space_left_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.total_space_gb'))), 2) AS total_space_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.used_space_gb'))), 2) AS used_space_gb
                FROM ynh_osquery
                WHERE ynh_osquery.name = 'memory_available_snapshot'
                AND ynh_osquery.packed = 0
                GROUP BY ynh_osquery.ynh_server_id, ynh_osquery.calendar_time
            ");

            YnhOsquery::where('name', 'memory_available_snapshot')
                ->where('packed', false)
                ->delete();
        });
    }
}
