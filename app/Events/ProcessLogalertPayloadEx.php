<?php

namespace App\Events;

use App\Models\YnhServer;

class ProcessLogalertPayloadEx extends ProcessLogalertPayload
{
    public function __construct(YnhServer $server, array $events)
    {
        parent::__construct($server, $events);
    }
}
