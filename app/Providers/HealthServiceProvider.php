<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseTableSizeCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Health::checks([
            CacheCheck::new(),
            DatabaseCheck::new(),
            DatabaseTableSizeCheck::new()
                ->table('telescope_entries', 1500),
            DebugModeCheck::new()->unless(app()->environment('local')),
            OptimizedAppCheck::new()->unless(app()->environment('local')),
            QueueCheck::new()->name('DefaultQueue')->onQueue('default')
                ->failWhenHealthJobTakesLongerThanMinutes(10),
            QueueCheck::new()->name('LowQueue')->onQueue('low')
                ->failWhenHealthJobTakesLongerThanMinutes(10),
            QueueCheck::new()->name('MediumQueue')->onQueue('medium')
                ->failWhenHealthJobTakesLongerThanMinutes(5),
            QueueCheck::new()->name('CriticalQueue')->onQueue('critical')
                ->failWhenHealthJobTakesLongerThanMinutes(2),
            QueueCheck::new()->name('ScoutQueue')->onQueue('scout')
                ->failWhenHealthJobTakesLongerThanMinutes(10),
            ScheduleCheck::new(),
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(80)
                ->failWhenUsedSpaceIsAbovePercentage(90),
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
