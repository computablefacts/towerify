<?php

namespace App\Listeners;

use App\Events\SendAuditReport;
use App\Mail\AuditReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
            $email = Str::replace('computablefacts.com', 'cywise.io', config('towerify.admin.email'));
            Mail::to($user->email)->bcc($email)->send($report['report']);
        }
    }
}
