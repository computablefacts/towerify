<?php

namespace App\Events;

use App\Models\Invitation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendInvitation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Invitation $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }
}
