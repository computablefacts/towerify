<?php

namespace App\Events;

use App\Models\YnhOrder;
use App\Models\YnhServer;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstallApp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $uid;
    public User $user;
    public YnhServer $server;
    public YnhOrder $order;

    public function __construct(string $uid, User $user, YnhServer $server, YnhOrder $order)
    {
        $this->uid = $uid;
        $this->user = $user;
        $this->server = $server;
        $this->order = $order;
    }
}
