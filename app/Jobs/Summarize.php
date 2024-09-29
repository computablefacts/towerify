<?php

namespace App\Jobs;

use App\Models\YnhOsquery;
use App\Models\YnhOsqueryDiskUsage;
use App\Models\YnhOsqueryMemoryUsage;
use App\Models\YnhOverview;
use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Models\Asset;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Summarize implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public static function monitoredServers(): int
    {
        return YnhServer::count();
    }

    public static function numberOfVulnerabilitiesByLevel(): array
    {
        return Asset::all()
            ->map(function (Asset $asset) {
                return [
                    'high' => $asset->alerts()->where('level', 'High')->count(),
                    'high_unverified' => $asset->alerts()->where('level', 'High (unverified)')->count(),
                    'medium' => $asset->alerts()->where('level', 'Medium')->count(),
                    'low' => $asset->alerts()->where('level', 'Low')->count(),
                ];
            })
            ->reduce(function (array $carry, array $counts) {
                return [
                    'high' => $carry['high'] + $counts['high'],
                    'high_unverified' => $carry['high_unverified'] + $counts['high_unverified'],
                    'medium' => $carry['medium'] + $counts['medium'],
                    'low' => $carry['low'] + $counts['low'],
                ];
            }, [
                'high' => 0,
                'high_unverified' => 0,
                'medium' => 0,
                'low' => 0,
            ]);
    }

    public static function monitoredIps(): int
    {
        return Asset::where('type', AssetTypesEnum::IP)->where('is_monitored', true)->count();
    }

    public static function monitoredDns(): int
    {
        return Asset::where('type', AssetTypesEnum::DNS)->where('is_monitored', true)->count();
    }

    public static function collectedMetrics(Collection $servers): int
    {
        $summary = self::summary();
        $cutOffTime = $summary?->updated_at;

        $diskUsage = YnhOsqueryDiskUsage::query()
            ->whereIn('ynh_server_id', $servers->pluck('id'));

        if ($cutOffTime) {
            $diskUsage->where('updated_at', '>=', $cutOffTime);
        }

        $memoryUsage = YnhOsqueryMemoryUsage::query()
            ->whereIn('ynh_server_id', $servers->pluck('id'));

        if ($cutOffTime) {
            $memoryUsage->where('updated_at', '>=', $cutOffTime);
        }

        $osquery = YnhOsquery::select('ynh_osquery.*')
            ->whereIn('name', ['disk_available_snapshot', 'memory_available_snapshot'])
            ->whereIn('ynh_server_id', $servers->pluck('id'));

        if ($cutOffTime) {
            $osquery->where('updated_at', '>=', $cutOffTime);
        }
        return ($summary ? $summary->collected_metrics : 0) + $diskUsage->count() + $memoryUsage->count() + $osquery->count();
    }

    public static function collectedEvents(Collection $servers): int
    {
        $summary = self::summary();
        $cutOffTime = $summary?->updated_at;

        $query = YnhOsquery::select('ynh_osquery.*')
            ->whereNotIn('name', ['disk_available_snapshot', 'memory_available_snapshot'])
            ->whereIn('ynh_server_id', $servers->pluck('id'));

        if ($cutOffTime) {
            $query->where('updated_at', '>=', $cutOffTime);
        }
        return ($summary ? $summary->collected_events : 0) + $query->count();
    }

    private static function summary(): ?YnhOverview
    {
        return YnhOverview::query()
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->first();
    }

    public function handle()
    {
        User::all()
            ->each(function ($user) {

                Auth::login($user); // otherwise the tenant will not be properly set

                $servers = YnhServer::forUser($user);
                $nbVulnerabilities = self::numberOfVulnerabilitiesByLevel();
                $summary = YnhOverview::create([
                    'monitored_servers' => self::monitoredServers(),
                    'monitored_ips' => self::monitoredIps(),
                    'monitored_dns' => self::monitoredDns(),
                    'collected_metrics' => self::collectedMetrics($servers),
                    'collected_events' => self::collectedEvents($servers),
                    'vulns_high' => $nbVulnerabilities['high'],
                    'vulns_high_unverified' => $nbVulnerabilities['high_unverified'],
                    'vulns_medium' => $nbVulnerabilities['medium'],
                    'vulns_low' => $nbVulnerabilities['low'],
                ]);
            });
    }
}
