<?php

namespace App\Events;

use App\Models\YnhServer;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateBackup
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
}
