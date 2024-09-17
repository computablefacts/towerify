<?php

namespace App\Modules\AdversaryMeter\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeginDiscovery
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tld;

    public function __construct(string $tld)
    {
        $this->tld = $tld;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
