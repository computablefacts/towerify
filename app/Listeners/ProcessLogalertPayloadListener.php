<?php

namespace App\Listeners;

use App\Events\ProcessLogalertPayload;
use Illuminate\Support\Facades\Log;

class ProcessLogalertPayloadListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::CRITICAL;
    }

    protected function handle2($event)
    {
        if (!($event instanceof ProcessLogalertPayload)) {
            throw new \Exception('Invalid event type!');
        }

        $server = $event->server;
        $events = $event->events;
        $nbEventsIn = count($events);
        $nbEventsOut = $server->addOsqueryEvents($events);

        Log::debug("LogAlert - nb_events_in={$nbEventsIn}, nb_events_out={$nbEventsOut}");
    }
}
