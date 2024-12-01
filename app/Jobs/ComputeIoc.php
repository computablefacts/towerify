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

    public function handle()
    {
        YnhServer::where('is_ready', true)
            ->where('is_frozen', false)
            ->get()
            ->each(function (YnhServer $server) {

                $dateMax = Carbon::now();
                $dateMin = $dateMax->copy()->subDay();
                $ioc = $server->ioc($dateMin, $dateMax);

                YnhIoc::create([
                    'ynh_server_id' => $server->id,
                    'date_min' => $dateMin,
                    'date_max' => $dateMax,
                    'score' => $ioc,
                ]);
            });
    }
}
