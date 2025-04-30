<?php

namespace App\Listeners;

use App\Events\RebuildLatestEventsCache;
use App\Models\YnhOsquery;
use App\Models\YnhOsqueryLatestEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RebuildLatestEventsCacheListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::LOW;
    }

    protected function handle2($event)
    {
        if (!($event instanceof RebuildLatestEventsCache)) {
            throw new \Exception('Invalid event type!');
        }

        $server = $event->server;

        // For each event type, update the cache of the latest events
        YnhOsquery::select('name')->whereNotIn('name', ['socket_events', 'listening_ports']/** see ComputeIoc */)->distinct()->pluck('name')->each(function (string $name) use ($server) {

            Log::debug("Updating cache for server {$server->name} and event type {$name}");

            // Copy the most recent events to the cache
            YnhOsquery::where('ynh_server_id', $server->id)
                ->where('name', $name)
                ->orderBy('calendar_time', 'desc')
                ->limit(100)
                ->get()
                ->each(function (YnhOsquery $event) use ($server) {
                    YnhOsqueryLatestEvent::updateOrCreate([
                        'ynh_server_id' => $server->id,
                        'ynh_osquery_id' => $event->id,
                        'event_name' => $event->name,
                    ], [
                        'ynh_server_id' => $server->id,
                        'ynh_osquery_id' => $event->id,
                        'event_name' => $event->name,
                        'calendar_time' => $event->calendar_time,
                        'server_name' => $server->name,
                        'updated' => true,
                    ]);
                });

            $added = YnhOsqueryLatestEvent::where('ynh_server_id', $server->id)
                ->where('event_name', $name)
                ->where('updated', true)
                ->count();
            $deleted = YnhOsqueryLatestEvent::where('ynh_server_id', $server->id)
                ->where('event_name', $name)
                ->where('updated', false)
                ->count();

            Log::debug("{$added} events added to cache. {$deleted} events removed from cache.");

            // Remove out of scope events
            DB::transaction(function () use ($server, $name) {
                YnhOsqueryLatestEvent::where('ynh_server_id', $server->id)
                    ->where('event_name', $name)
                    ->where('updated', false)
                    ->delete();
                YnhOsqueryLatestEvent::where('ynh_server_id', $server->id)
                    ->where('event_name', $name)
                    ->update(['updated' => false]);
            });
        });
    }
}
