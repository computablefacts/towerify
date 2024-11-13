<?php

namespace App\Console;

use App\Jobs\AgeOffOsqueryEvents;
use App\Jobs\PullServersInfos;
use App\Jobs\Summarize;
use App\Modules\AdversaryMeter\Jobs\FixDanglingScans;
use App\Modules\AdversaryMeter\Jobs\SendAuditReport;
use App\Modules\AdversaryMeter\Jobs\TriggerDiscoveryShallow;
use App\Modules\AdversaryMeter\Jobs\TriggerScan;
use App\Modules\CyberBuddy\Jobs\DeleteEmbeddedChunks;
use App\Modules\CyberBuddy\Jobs\EmbedChunks;
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
        // $schedule->job(new CheckServersHealth())->everyFifteenMinutes();
        $schedule->job(new PullServersInfos())->hourly();
        $schedule->job(new AgeOffOsqueryEvents())->hourly();
        // $schedule->job(new DownloadDebianSecurityBugTracker())->daily();
        $schedule->job(new Summarize())->daily();
        // $schedule->job(new RebuildPackagesList())->daily();
        $schedule->command('telescope:prune --hours=48')->daily();

        // AdversaryMeter
        $schedule->job(new TriggerScan())->everyMinute();
        $schedule->job(new FixDanglingScans())->hourly();
        $schedule->job(new TriggerDiscoveryShallow())->daily();
        $schedule->job(new SendAuditReport())->dailyAt('6:45');
        // $schedule->job(new TriggerDiscoveryDeep())->weekly();

        // CyberBuddy
        $schedule->job(new EmbedChunks())->everyMinute();
        $schedule->job(new DeleteEmbeddedChunks())->everyMinute();
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
