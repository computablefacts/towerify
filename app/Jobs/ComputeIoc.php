<?php

namespace App\Jobs;

use App\Models\YnhIoc;
use App\Models\YnhServer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ComputeIoc implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public static function variance(Collection $numbers): float
    {
        $mean = $numbers->average();
        return $numbers->reduce(fn($carry, $number) => $carry + pow(abs($number - $mean), 2), 0.0) / $numbers->count();
    }

    public static function stdDev(Collection $numbers): float
    {
        return sqrt(self::variance($numbers));
    }

    public static function isAnomaly(float $number, Collection $numbers): bool
    {
        $mean = $numbers->average();
        $stdDev = self::stdDev($numbers);
        return $number < $mean - 2 * $stdDev || $number > $mean + 2 * $stdDev;
    }

    public function handle()
    {
        YnhServer::where('is_ready', true)
            ->where('is_frozen', false)
            ->get()
            ->each(function (YnhServer $server) {

                // The last IOC computed
                /** @var YnhIoc $ioc */
                $ioc = YnhIoc::where('ynh_server_id', $server->id)
                    ->orderBy('date_max', 'desc')
                    ->limit(1)
                    ->first();

                $dateEnd = Carbon::now();
                $dateBegin = $ioc ? $ioc->date_max : $dateEnd->copy()->subDays(10);

                for ($dateMin = $dateBegin; $dateMin->lt($dateEnd); $dateMin->addMinutes(10)) {

                    // Load the new data points
                    $dateMax = $dateMin->copy()->addMinutes(10);
                    $iocs = $server->iocs($dateMin, $dateMax);
                    $numbers = $iocs->pluck('rule_score');

                    if ($numbers->isEmpty()) { // Nothing happened!
                        /** @var YnhIoc $ioc */
                        $ioc = YnhIoc::create([
                            'ynh_server_id' => $server->id,
                            'date_min' => $dateMin,
                            'date_max' => $dateMax,
                        ]);
                    } else { // Compute the next IOC
                        /** @var YnhIoc $ioc */
                        $ioc = YnhIoc::create([
                            'ynh_server_id' => $server->id,
                            'date_min' => $dateMin,
                            'date_max' => $dateMax,
                            'iocs' => $iocs,
                            'count' => $numbers->count(),
                            'min' => $numbers->min(),
                            'max' => $numbers->max(),
                            'sum' => $numbers->sum(),
                            'mean' => $numbers->average(),
                            'median' => $numbers->median(),
                            'std_dev' => self::stdDev($numbers),
                            'variance' => self::variance($numbers),
                        ]);
                    }

                    // Anomaly detection
                    $scores = YnhIoc::where('id', '<>', $ioc->id)
                        ->where('ynh_server_id', $server->id)
                        ->orderBy('date_max', 'desc')
                        ->limit(100)
                        ->get()
                        ->pluck('sum');

                    if ($scores->isNotEmpty()) {
                        $ioc->is_anomaly = self::isAnomaly($ioc->sum == null ? 0 : $ioc->sum, $scores);
                        $ioc->save();
                    }
                }
            });
    }
}
