<?php

namespace App\Events;

use App\Models\Asset;
use Illuminate\Broadcasting\InteractsWithSockets;
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

    public function asset(): ?Asset
    {
        return Asset::find($this->assetId);
    }
}
