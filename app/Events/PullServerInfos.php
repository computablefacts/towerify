<?php

namespace App\Events;

use App\Models\YnhServer;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PullServerInfos
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;
    public User $user;
    public YnhServer $server;

    public function __construct(string $uid, User $user, YnhServer $server)
    {
        $this->uid = $uid;
        $this->user = $user;
        $this->server = $server;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
