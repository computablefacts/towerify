<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Mail\AuditReport;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/** @deprecated */
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

    public function handle()
    {
        User::where('is_active', true)
            ->get()
            ->filter(fn(User $user) => $user->canUseAdversaryMeter())
            ->each(function (User $user) {

                Auth::login($user); // otherwise the tenant will not be properly set

                $alerts = Asset::where('is_monitored', true)->get()->flatMap(fn(Asset $asset) => $asset->alerts()->get());
                $alertsHigh = $alerts->filter(fn(Alert $alert) => $alert->level === 'High');
                $alertsMedium = $alerts->filter(fn(Alert $alert) => $alert->level === 'Medium');
                $alertsLow = $alerts->filter(fn(Alert $alert) => $alert->level === 'Low');
                $assetsMonitored = Asset::where('is_monitored', true)->orderBy('asset')->get();
                $assetsNotMonitored = Asset::where('is_monitored', false)->orderBy('asset')->get();
                $assetsDiscovered = Asset::where('created_by', '>=', Carbon::now()->subDay())->orderBy('asset')->get();

                if ($alerts->count() > 0 || $assetsMonitored->count() > 0 || $assetsNotMonitored->count() > 0 || $assetsDiscovered->count() > 0) {
                    Mail::to($user->email)->send(new AuditReport($alertsHigh, $alertsMedium, $alertsLow, $assetsMonitored, $assetsNotMonitored, $assetsDiscovered));
                }
            });
    }
}
