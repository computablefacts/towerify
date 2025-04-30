<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndDiscovery
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $start;
    public string $tld;
    public string $taskId;

    public function __construct(Carbon $start, string $tld, string $taskId)
    {
        $this->start = $start;
        $this->tld = $tld;
        $this->taskId = $taskId;
    }

    public function sink(): void
    {
        EndDiscovery::dispatch($this->start, $this->tld, $this->taskId);
    }

    public function drop(): bool
    {
        $dropAfter = config('towerify.adversarymeter.drop_discovery_events_after_x_minutes');
        return Carbon::now()->diffInMinutes($this->start, true) > $dropAfter;
    }
}
