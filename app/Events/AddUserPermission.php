<?php

namespace App\Events;

use App\Models\YnhServer;
use App\Models\YnhUser;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddUserPermission
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;
    public User $user;
    public YnhServer $server;
    public YnhUser $ynhUser;
    public string $permission;

    public function __construct(string $uid, User $user, YnhServer $server, YnhUser $ynhUser, string $permission)
    {
        $this->uid = $uid;
        $this->user = $user;
        $this->server = $server;
        $this->ynhUser = $ynhUser;
        $this->permission = $permission;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
