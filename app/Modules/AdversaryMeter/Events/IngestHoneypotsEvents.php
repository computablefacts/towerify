<?php

namespace App\Modules\AdversaryMeter\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IngestHoneypotsEvents
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $timestamp;
    public string $dns;
    public array $events;

    public function __construct(Carbon $timestamp, string $dns, array $events)
    {
        $this->timestamp = $timestamp;
        $this->dns = $dns;
        $this->events = $events;
    }
}
