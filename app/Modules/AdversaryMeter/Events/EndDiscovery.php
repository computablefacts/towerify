<?php

namespace App\Modules\AdversaryMeter\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
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

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    public function sink(): void
    {
        event(new EndDiscovery($this->start, $this->tld, $this->taskId));
    }

    public function drop(): bool
    {
        $dropAfter = config('towerify.adversarymeter.drop_discovery_events_after_x_minutes');
        return Carbon::now()->diffInMinutes($this->start, true) > $dropAfter;
    }
}
