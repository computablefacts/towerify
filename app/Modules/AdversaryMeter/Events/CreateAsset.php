<?php

namespace App\Modules\AdversaryMeter\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateAsset
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $asset;
    public ?int $userId;
    public ?int $customerId;
    public ?int $tenantId;

    public function __construct(string $asset, ?int $userId = null, ?int $customerId = null, ?int $tenantId = null)
    {
        $this->asset = $asset;
        $this->userId = $userId;
        $this->customerId = $customerId;
        $this->tenantId = $tenantId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
