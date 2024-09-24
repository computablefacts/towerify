<?php

namespace App\Modules\CyberBuddy\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IngestFile
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $collection;
    public string $url;

    public function __construct(User $user, string $collection, string $url)
    {
        $this->user = $user;
        $this->collection = $collection;
        $this->url = $url;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
