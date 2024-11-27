<?php

namespace App\Modules\AdversaryMeter\Events;

use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndPortsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $start;
    public int $assetId;
    public int $scanId;

    public function __construct(Carbon $start, Asset $asset, Scan $scan)
    {
        $this->start = $start;
        $this->assetId = $asset->id;
        $this->scanId = $scan->id;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function asset(): ?Asset
    {
        return Asset::find($this->assetId);
    }

    public function scan(): ?Scan
    {
        return Scan::find($this->scanId);
    }

    public function sink(): void
    {
        EndPortsScan::dispatch($this->start, $this->asset(), $this->scan());
    }

    public function drop(): bool
    {
        $dropAfter = config('towerify.adversarymeter.drop_scan_events_after_x_minutes');
        return Carbon::now()->diffInMinutes($this->start, true) > $dropAfter;
    }
}
