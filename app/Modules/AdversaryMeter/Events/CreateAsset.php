<?php

namespace App\Modules\AdversaryMeter\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateAsset
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $asset;

    public function __construct(User $user, string $asset)
    {
        $this->user = $user;
        $this->asset = $asset;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
