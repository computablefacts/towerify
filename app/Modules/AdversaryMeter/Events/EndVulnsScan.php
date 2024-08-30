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
    public int $iteration;

    public function __construct(Scan $scan, int $iteration)
    {
        $this->scanId = $scan->id;
        $this->iteration = $iteration;
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
