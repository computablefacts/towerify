<?php

namespace App\Modules\AdversaryMeter\Events;

use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Scan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndPortsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $assetId;
    public int $scanId;

    public function __construct(Asset $asset, Scan $scan)
    {
        $this->assetId = $asset->id;
        $this->scanId = $scan->id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function asset(): Asset
    {
        return Asset::find($this->assetId);
    }

    public function scan(): Scan
    {
        return Scan::find($this->scanId);
    }
}
