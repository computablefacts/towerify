<?php

namespace App\Jobs;

use App\Models\YnhOsquery;
use App\Models\YnhOsqueryEventsCount;
use App\Models\YnhOsqueryLatestEvent;
use App\Models\YnhOsqueryRule;
use App\Models\YnhServer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cleanup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function handle()
    {
        Log::debug('Cleanup begins');

        // When a rule is disabled, cleanup the history
        $rules = YnhOsqueryRule::where('enabled', false)->get()->pluck('name');
        YnhOsquery::whereIn('name', $rules)->limit(10000)->delete();
        YnhOsqueryLatestEvent::whereIn('event_name', $rules)->delete();

        Log::debug('Cleanup completed');

        // When the list of cached events "overflow" for a given (server, rule), remove the oldest events
        $threshold = 1000;

        $overflowingEvents = DB::table('ynh_osquery_latest_events')
            ->select('ynh_server_id', 'server_name', 'event_name', DB::raw('COUNT(*) as event_count'))
            ->whereNotIn('event_name', $rules)
            ->groupBy('ynh_server_id', 'server_name', 'event_name')
            ->having('event_count', '>', $threshold)
            ->get();

        Log::debug("Found {$overflowingEvents->count()} overflowing events");

        foreach ($overflowingEvents as $event) {
            Log::debug("Compacting {$event->event_name} for {$event->server_name}");
            DB::table('ynh_osquery_latest_events')
                ->where('ynh_server_id', $event->ynh_server_id)
                ->where('event_name', $event->event_name)
                ->orderBy('calendar_time')
                ->limit($event->event_count - $threshold)
                ->delete();
        }

        Log::debug('Group server events by increments of 10 minutes');

        YnhServer::where('is_ready', true)
            ->where('is_frozen', false)
            ->get()
            ->each(function (YnhServer $server) {

                /** @var YnhOsqueryEventsCount $ec */
                $ec = YnhOsqueryEventsCount::where('ynh_server_id', $server->id)
                    ->orderBy('date_max', 'desc')
                    ->limit(1)
                    ->first();

                $dateEnd = Carbon::now();
                $dateBegin = $ec ? $ec->date_max : $dateEnd->copy()->subDays(10);

                for ($dateMin = $dateBegin; $dateMin->lt($dateEnd); $dateMin->addMinutes(10)) {

                    $dateMax = $dateMin->copy()->addMinutes(10);
                    $events = $server->osqueryEvents($dateMin, $dateMax);

                    Log::debug("Grouping {$events->count()} events for server {$server->name}");

                    if ($events->isEmpty()) { // Nothing happened!
                        /** @var YnhOsqueryEventsCount $count */
                        $count = YnhOsqueryEventsCount::create([
                            'ynh_server_id' => $server->id,
                            'date_min' => $dateMin,
                            'date_max' => $dateMax,
                        ]);
                    } else { // Compute the next count
                        /** @var YnhOsqueryEventsCount $count */
                        $count = YnhOsqueryEventsCount::create([
                            'ynh_server_id' => $server->id,
                            'date_min' => $dateMin,
                            'date_max' => $dateMax,
                            'count' => $events->count(),
                            'events' => $events,
                        ]);
                    }
                }
            });
    }
}
