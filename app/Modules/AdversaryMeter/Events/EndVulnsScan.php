<?php

namespace App\Modules\AdversaryMeter\Events;

use App\Modules\AdversaryMeter\Models\Scan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndVulnsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $scanId;

    public function __construct(Scan $scan)
    {
        $this->scanId = $scan->id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function scan(): Scan
    {
        return Scan::find($this->scanId);
    }
}
