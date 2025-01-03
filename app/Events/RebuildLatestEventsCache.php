<?php

namespace App\Events;

use App\Models\YnhServer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RebuildLatestEventsCache
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public YnhServer $server;

    public function __construct(YnhServer $server = null)
    {
        $this->server = $server;
    }

    public static function sink()
    {
        YnhServer::all()->each(fn(YnhServer $server) => RebuildLatestEventsCache::dispatch($server));
    }
}
