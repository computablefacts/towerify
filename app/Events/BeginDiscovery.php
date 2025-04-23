<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BeginDiscovery
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tld;

    public function __construct(string $tld)
    {
        $this->tld = $tld;
    }
}
