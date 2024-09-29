<?php

namespace App\Jobs;

use App\Models\YnhOsquery;
use App\Models\YnhOsqueryDiskUsage;
use App\Models\YnhOsqueryMemoryUsage;
use App\Models\YnhServer;
use App\Models\YnhSummary;
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

    private static function summary(): ?YnhSummary
    {
        return YnhSummary::query()
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
                $summary = YnhSummary::create([
                    'monitored_ips' => self::monitoredIps(),
                    'monitored_dns' => self::monitoredDns(),
                    'collected_metrics' => self::collectedMetrics($servers),
                    'collected_events' => self::collectedEvents($servers),
                ]);
            });
    }
}
