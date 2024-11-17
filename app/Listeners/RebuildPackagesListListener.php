<?php

namespace App\Listeners;

use App\Events\RebuildPackagesList;
use App\Models\Tenant;
use App\Models\YnhCve;
use App\Models\YnhOsquery;
use App\Models\YnhOsqueryPackage;
use App\Models\YnhServer;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RebuildPackagesListListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof RebuildPackagesList)) {
            throw new \Exception('Invalid event type!');
        }

        Tenant::all()
            ->map(fn(Tenant $tenant) => User::where('tenant_id', $tenant->id)->orderBy('created_at')->first())
            ->filter(fn(?User $user) => isset($user))
            ->each(function (User $user) {

                Auth::login($user); // otherwise the tenant will not be properly set

                YnhServer::all()
                    ->each(function (YnhServer $server) {
                        try {

                            $osInfo = YnhOsquery::osInfos(collect([$server]))->first();

                            if ($osInfo) {

                                YnhOsqueryPackage::where('ynh_server_id', $server->id)->delete();

                                /** @var YnhOsquery $latest */
                                $latest = YnhOsquery::where('ynh_server_id', $server->id)
                                    ->where('name', 'deb_packages_installed_snapshot')
                                    ->orderBy('calendar_time', 'desc')
                                    ->first();

                                if ($latest) { // use our debian-specific implementation because we deal with apt, snap, dpkg, etc.
                                    $installed = YnhOsquery::where('ynh_server_id', $server->id)
                                        ->where('name', 'deb_packages_installed_snapshot')
                                        ->where('unix_time', $latest->unix_time)
                                        ->where('calendar_time', $latest->calendar_time)
                                        ->whereJsonContains('columns', ['uid' => $latest->columns['uid']])
                                        ->orderBy('calendar_time', 'desc')
                                        ->get();
                                } else {

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
                                                return !collect($uninstalled[$key])->contains(fn(YnhOsquery $e) => $e->calendar_time->isAfter($event->calendar_time));
                                            }
                                            return true;
                                        });
                                }

                                // Save snapshot!
                                $installed->each(function (YnhOsquery $event) use ($server, $osInfo) {

                                    $cves = YnhCve::appCves($osInfo->os, $osInfo->codename, $event->columns['name'], $event->columns['version']);

                                    if ($cves->isEmpty()) {
                                        YnhOsqueryPackage::create([
                                            'ynh_server_id' => $server->id,
                                            'ynh_cve_id' => null,
                                            'os' => $osInfo->os,
                                            'os_version' => $osInfo->codename,
                                            'package' => $event->columns['name'],
                                            'package_version' => $event->columns['version'],
                                            'cves' => [],
                                        ]);
                                    } else {
                                        $cves->each(function (YnhCve $cve) use ($server, $osInfo, $event, $cves) {
                                            YnhOsqueryPackage::create([
                                                'ynh_server_id' => $server->id,
                                                'ynh_cve_id' => $cve->id,
                                                'os' => $osInfo->os,
                                                'os_version' => $osInfo->codename,
                                                'package' => $event->columns['name'],
                                                'package_version' => $event->columns['version'],
                                                'cves' => $cves->pluck('id')->toArray(),
                                            ]);
                                        });
                                    }
                                });
                            }
                        } catch (\Exception $exception) {
                            Log::error($exception->getMessage());
                        }
                    });

                Auth::logout();
            });
    }
}
