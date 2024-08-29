<?php

namespace App\Modules\AdversaryMeter\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndDiscovery
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tld;
    public string $taskId;

    public function __construct(string $tld, string $taskId)
    {
        $this->tld = $tld;
        $this->taskId = $taskId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
