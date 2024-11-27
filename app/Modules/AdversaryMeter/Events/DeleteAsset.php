<?php

namespace App\Modules\AdversaryMeter\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteAsset
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $asset;

    public function __construct(User $user, string $asset)
    {
        $this->user = $user;
        $this->asset = $asset;
    }
}
