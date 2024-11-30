<?php

namespace App\Modules\AdversaryMeter\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\AdversaryMeter\Events\SendAuditReport;
use App\Modules\AdversaryMeter\Mail\AuditReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendAuditReportListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::CRITICAL;
    }

    protected function handle2($event)
    {
        if (!($event instanceof SendAuditReport)) {
            throw new \Exception('Invalid event type!');
        }

        $user = $event->user;
        Auth::login($user); // otherwise the tenant will not be properly set
        $report = AuditReport::create();

        if (!$report['is_empty']) {
            Mail::to($user->email)->send($report['report']);
        }
    }
}
