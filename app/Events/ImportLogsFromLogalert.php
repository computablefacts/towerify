<?php

namespace App\Events;

use App\Models\YnhServer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportLogsFromLogalert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public YnhServer $server;
    public array $events;

    public function __construct(YnhServer $server, array $events)
    {
        $this->server = $server;
        $this->events = $events;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
