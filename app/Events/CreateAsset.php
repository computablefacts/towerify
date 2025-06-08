<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateAsset
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $asset;
    public bool $monitor;
    public array $tags;

    public function __construct(User $user, string $asset, bool $monitor, array $tags = [])
    {
        $this->user = $user;
        $this->asset = $asset;
        $this->monitor = $monitor;
        $this->tags = $tags;
    }
}
