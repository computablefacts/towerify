<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Mail\AuditReport;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendAuditReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function viaQueue(): string
    {
        return 'critical';
    }

    public function handle()
    {
        User::where('is_active', true)
            ->get()
            ->filter(fn(User $user) => $user->canUseAdversaryMeter())
            ->each(function (User $user) {

                Auth::login($user); // otherwise the tenant will not be properly set
                $report = AuditReport::create();

                if (!$report['is_empty']) {
                    Mail::to($user->email)->send($report['report']);
                }
            });
    }
}
