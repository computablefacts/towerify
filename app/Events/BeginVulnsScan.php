<?php

namespace App\Events;

use App\Models\Port;
use App\Models\Scan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeginVulnsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $scanId;
    public int $portId;

    public function __construct(Scan $scan, Port $port)
    {
        $this->scanId = $scan->id;
        $this->portId = $port->id;
    }

    public function scan(): ?Scan
    {
        return Scan::find($this->scanId);
    }

    public function port(): ?Port
    {
        return Port::find($this->portId);
    }
}
