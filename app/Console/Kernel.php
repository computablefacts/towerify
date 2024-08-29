<?php

namespace App\Console;

use App\Jobs\AgeOffOsqueryEvents;
use App\Jobs\CheckServersHealth;
use App\Jobs\PullServersInfos;
use App\Jobs\UpdateShadowIt;
use App\Modules\AdversaryMeter\Jobs\TriggerDiscovery;
use App\Modules\AdversaryMeter\Jobs\TriggerScan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new PullServersInfos())->hourly();
        $schedule->job(new AgeOffOsqueryEvents())->hourly();
        $schedule->job(new CheckServersHealth())->everyFifteenMinutes();
        $schedule->job(new TriggerScan())->everyMinute();
        $schedule->job(new TriggerDiscovery())->daily();
        $schedule->command('telescope:prune --hours=48')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
