<?php

namespace App\Events;

use App\Models\YnhServer;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddTwrUserPermission
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;
    public User $user;
    public YnhServer $server;
    public User $ynhUser;
    public string $permission;

    public function __construct(string $uid, User $user, YnhServer $server, User $ynhUser, string $permission)
    {
        $this->uid = $uid;
        $this->user = $user;
        $this->server = $server;
        $this->ynhUser = $ynhUser;
        $this->permission = $permission;
    }
}
