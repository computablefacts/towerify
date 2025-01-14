<?php

namespace App\Listeners;

use App\Events\SendInvitation;
use App\Mail\Invitation;
use Illuminate\Support\Facades\Mail;

class SendInvitationListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::MEDIUM;
    }

    protected function handle2($event)
    {
        if (!($event instanceof SendInvitation)) {
            throw new \Exception('Invalid event type!');
        }

        $invitation = $event->invitation;
        Mail::to($invitation->email)->send(new Invitation($event->invitation));
    }
}
