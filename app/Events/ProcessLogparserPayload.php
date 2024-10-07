<?php

namespace App\Events;

use App\Models\YnhServer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProcessLogparserPayload
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public YnhServer $server;
    public Collection $logs;

    public function __construct(YnhServer $server, Collection $logs)
    {
        $this->server = $server;
        $this->logs = $logs;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
