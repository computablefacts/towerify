<?php

namespace App\Jobs;

use App\Models\YnhCve;
use App\Models\YnhOsquery;
use App\Models\YnhOsqueryPackage;
use App\Models\YnhServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RebuildPackagesList implements ShouldQueue
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
        YnhServer::all()->each(function (YnhServer $server) {
            DB::transaction(function () use ($server) {

                $osInfo = YnhOsquery::osInfos(collect([$server]))->first();

                if ($osInfo) {

                    YnhOsqueryPackage::where('ynh_server_id', $server->id)->delete();

                    // The list of uninstalled packages
                    $uninstalled = YnhOsquery::where('ynh_server_id', $server->id)
                        ->where('name', 'deb_packages')
                        ->where('action', 'removed')
                        ->orderBy('calendar_time', 'desc')
                        ->get()
                        ->groupBy(fn(YnhOsquery $event) => $event->columns['name'] . $event->columns['version']);

                    // The list of installed packages
                    $installed = YnhOsquery::where('ynh_server_id', $server->id)
                        ->where('name', 'deb_packages')
                        ->where('action', 'added')
                        ->orderBy('calendar_time', 'desc')
                        ->get()
                        ->filter(function (YnhOsquery $event) use ($uninstalled) { // filter out installed then uninstalled packages

                            $key = $event->columns['name'] . $event->columns['version'];

                            if (isset($uninstalled[$key])) {
                                return !collect($uninstalled[$key])->hasAny(fn(YnhOsquery $e) => $e->calendar_time->isAfter($event->calendar_time));
                            }
                            return true;
                        });

                    // Save snapshot!
                    $installed->each(function (YnhOsquery $event) use ($server, $osInfo) {
                        YnhOsqueryPackage::create([
                            'ynh_server_id' => $server->id,
                            'os' => $osInfo->os,
                            'os_version' => $osInfo->codename,
                            'package' => $event->columns['name'],
                            'package_version' => $event->columns['version'],
                            'cves' => YnhCve::appCves($osInfo->os, $osInfo->codename, $event->columns['name'], $event->columns['version']),
                        ]);
                    });
                }
            });
        });
    }
}