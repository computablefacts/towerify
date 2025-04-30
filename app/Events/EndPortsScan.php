<?php

namespace App\Events;

use App\Models\Asset;
use App\Models\Scan;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndPortsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $start;
    public int $assetId;
    public int $scanId;
    public array $taskResult;

    public function __construct(Carbon $start, Asset $asset, Scan $scan, array $taskResult = [])
    {
        $this->start = $start;
        $this->assetId = $asset->id;
        $this->scanId = $scan->id;
        $this->taskResult = $taskResult;
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
