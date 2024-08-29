<?php

namespace App\Modules\AdversaryMeter\Events;

use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\Scan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeginVulnsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Scan $scan;
    public Port $port;

    public function __construct($scan, Port $port)
    {
        $this->scan = $scan;
        $this->port = $port;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
