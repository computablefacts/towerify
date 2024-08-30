<?php

namespace App\Modules\AdversaryMeter\Events;

use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeginPortsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $assetId;

    public function __construct(Asset $asset)
    {
        $this->assetId = $asset->id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function asset(): Asset
    {
        return Asset::find($this->assetId);
    }
}
