<?php

namespace App\Console;

use App\Jobs\Cleanup;
use App\Jobs\DeleteEmbeddedChunks;
use App\Jobs\DownloadDebianSecurityBugTracker;
use App\Jobs\EmbedChunks;
use App\Jobs\ProcessIncomingEmails;
use App\Jobs\PullServersInfos;
use App\Jobs\Summarize;
use App\Jobs\TriggerDiscoveryShallow;
use App\Jobs\TriggerScan;
use App\Jobs\TriggerSendAuditReport;
use App\Jobs\UpdateTables;
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
        $schedule->job(new Cleanup())->everyThreeMinutes();
        $schedule->job(new PullServersInfos())->everyThreeHours();
        $schedule->job(new Summarize())->everySixHours();
        $schedule->job(new DownloadDebianSecurityBugTracker())->daily();
        $schedule->command('telescope:prune --hours=48')->daily();

        // AdversaryMeter
        $schedule->job(new TriggerScan())->everyMinute();
        $schedule->job(new TriggerDiscoveryShallow())->daily();
        $schedule->job(new TriggerSendAuditReport())->dailyAt('6:45');
        // $schedule->job(new TriggerDiscoveryDeep())->weekly();

        // CyberBuddy
        $schedule->job(new EmbedChunks())->everyMinute();
        $schedule->job(new DeleteEmbeddedChunks())->everyMinute();
        $schedule->job(new ProcessIncomingEmails())->everyMinute();
        $schedule->job(new UpdateTables())->everyMinute();
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
