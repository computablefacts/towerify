<?php

namespace App\Modules\AdversaryMeter\Events;

use App\Modules\AdversaryMeter\Models\Scan;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndVulnsScan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $start;
    public int $scanId;

    public function __construct(Carbon $start, Scan $scan)
    {
        $this->start = $start;
        $this->scanId = $scan->id;
    }

    public function scan(): ?Scan
    {
        return Scan::find($this->scanId);
    }

    public function sink(): void
    {
        EndVulnsScan::dispatch($this->start, $this->scan());
    }

    public function drop(): bool
    {
        $dropAfter = config('towerify.adversarymeter.drop_scan_events_after_x_minutes');
        return Carbon::now()->diffInMinutes($this->start, true) > $dropAfter;
    }
}
