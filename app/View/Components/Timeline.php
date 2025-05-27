<?php

namespace App\View\Components;

use App\Helpers\JosianneClient;
use App\Helpers\Messages;
use App\Models\Alert;
use App\Models\Asset;
use App\Models\Conversation;
use App\Models\Honeypot;
use App\Models\HoneypotEvent;
use App\Models\Port;
use App\Models\PortTag;
use App\Models\Scan;
use App\Models\TimelineItem;
use App\Models\YnhServer;
use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Timeline extends Component
{
    const string CATEGORY_EVENTS = 'events';
    const string CATEGORY_NOTES = 'notes';
    const string CATEGORY_VULNERABILITIES = 'vulnerabilities';
    const string CATEGORY_CONVERSATIONS = 'conversations';

    public string $todaySeparator;
    public array $messages;
    public array $blacklist;
    public array $honeypots;
    public array $mostRecentHoneypotEvents;

    // Filters
    public array $assets;
    public int $assetId;
    public array $servers;
    public int $serverId;
    public array $dates;
    public string $dateId;
    public array $categories;
    public string $categoryId;

    public static function newSeparator(Carbon $date): string
    {
        $timestamp = $date->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');

        return Str::replace("\n", '', \Illuminate\Support\Facades\View::make('cywise._timeline-separator', [
            'date' => $date,
        ])->render());
    }

    public static function newLeak(User $user, TimelineItem $item): string
    {
        $timestamp = $item->timestamp->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-leak', [
            'date' => $date,
            'time' => $time,
            'user' => $user,
            'leak' => $item,
        ])->render();
    }

    public static function newNote(User $user, TimelineItem $item): string
    {
        $timestamp = $item->timestamp->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-note', [
            'date' => $date,
            'time' => $time,
            'user' => $user,
            'note' => $item,
        ])->render();
    }

    public static function newAsset(User $user, Asset $asset): string
    {
        $timestamp = $asset->created_at->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-asset', [
            'date' => $date,
            'time' => $time,
            'asset' => $asset,
        ])->render();
    }

    public static function newVulnerability(User $user, Alert $alert, Asset $asset, Port $port): string
    {
        $timestamp = $alert->updated_at->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        if ($alert->level === 'High') {
            $txtColor = "white";
            $bgColor = "#ff4d4d";
            $level = "(criticité haute)";
        } else if ($alert->level === 'Medium') {
            $txtColor = "white";
            $bgColor = "#ffaa00";
            $level = "(criticité moyenne)";
        } else if ($alert->level === 'Low') {
            $txtColor = "white";
            $bgColor = "#4bd28f";
            $level = "(criticité basse)";
        } else {
            $txtColor = "var(--c-grey-400)";
            $bgColor = "rgba(125, 188, 255, 0.6)";
            $level = "(niveau inconnu)";
        }

        $tags = "<div><span class='lozenge new' style='font-size: 0.8rem;margin-top: 3px;'>" . $port
                ->tags()
                ->get()
                ->map(fn(PortTag $tag) => Str::lower($tag->tag))
                ->join("</span>&nbsp;<span class='lozenge new' style='font-size: 0.8rem;margin-top: 3px;'>") . "</span></div>";

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-vulnerability', [
            'date' => $date,
            'time' => $time,
            'txtColor' => $txtColor,
            'bgColor' => $bgColor,
            'level' => $level,
            'tags' => $tags,
            'alert' => $alert,
            'asset' => $asset,
            'port' => $port,
        ])->render();
    }

    public static function newEvent(User $user, array $msg): string
    {
        $timestamp = $msg['timestamp'];
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-event', [
            'date' => $date,
            'time' => $time,
            'msg' => $msg,
        ])->render();
    }

    public static function newConversation(User $user, Conversation $conversation): string
    {
        $timestamp = $conversation->updated_at->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-conversation', [
            'date' => $date,
            'time' => $time,
            'conversation' => $conversation,
        ])->render();
    }

    public static function newScan(User $user, Asset $asset, Carbon $portsScanBeginsAt, ?Carbon $portsScanEndsAt, ?Carbon $vulnsScanBeginsAt, ?Carbon $vulnsScanEndsAt, int $nbRunningScans, int $nbCompletedScans): string
    {
        $timestamp = $portsScanBeginsAt->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-scan', [
            'date' => $date,
            'time' => $time,
            'asset' => $asset,
            'portsScanBeginsAt' => $portsScanBeginsAt,
            'portsScanEndsAt' => $portsScanEndsAt,
            'vulnsScanBeginsAt' => $vulnsScanBeginsAt,
            'vulnsScanEndsAt' => $vulnsScanEndsAt,
            'total' => $nbRunningScans + $nbCompletedScans,
            'remaining' => $nbRunningScans,
        ])->render();
    }

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $params = request()->query();
        $this->assetId = (int)($params['asset_id'] ?? 0);
        $this->serverId = (int)($params['server_id'] ?? 0);
        $this->dateId = $params['date'] ?? '';
        $this->categoryId = $params['category'] ?? self::CATEGORY_VULNERABILITIES;
        $this->todaySeparator = self::newSeparator(Carbon::now());
        $this->categories = [
            self::CATEGORY_VULNERABILITIES,
            self::CATEGORY_EVENTS,
            self::CATEGORY_NOTES,
            self::CATEGORY_CONVERSATIONS,
        ];

        $messages = collect();

        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_EVENTS) {
            $messages = $messages->concat($this->events($user));
        }
        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_VULNERABILITIES) {
            $messages = $messages->concat($this->assets($user))
                ->concat($this->scans($user))
                ->concat($this->vulnerabilities($user))
                ->concat($this->leaks($user));
        }
        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_NOTES) {
            $messages = $messages->concat($this->notes($user));
        }
        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_CONVERSATIONS) {
            $messages = $messages->concat($this->conversations($user));
        }

        $this->messages = $messages
            ->sortByDesc('timestamp')
            ->groupBy(fn(array $event) => $event['date'])
            ->mapWithKeys(function ($events, $timestamp) {
                return [
                    $timestamp => collect($events)->sortByDesc('time')->groupBy(fn(array $event) => $event['time'])
                ];
            })
            ->toArray();

        $this->dates = array_keys($this->messages);

        $this->assets = collect($this->messages)
            ->values()
            ->flatMap(fn(array $events) => array_values($events))
            ->map(fn(array $events) => $events[0])
            ->filter(fn(array $event) => isset($event['_asset']))
            ->unique(fn(array $event) => $event['_asset']->id)
            ->map(function (array $event) {
                /** @var Asset $asset */
                $asset = $event['_asset'];
                return [
                    'type' => 'asset',
                    'id' => $asset->id,
                    'name' => $asset->asset,
                    'high' => $asset->alerts()->where('level', 'High')->count(),
                    'medium' => $asset->alerts()->where('level', 'Medium')->count(),
                    'low' => $asset->alerts()->where('level', 'Low')->count(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $this->servers = collect($this->messages)
            ->values()
            ->flatMap(fn(array $events) => array_values($events))
            ->map(fn(array $events) => $events[0])
            ->filter(fn(array $event) => isset($event['_server']))
            ->unique(fn(array $event) => $event['_server']->id)
            ->map(function (array $event) {
                /** @var YnhServer $server */
                $server = $event['_server'];
                return [
                    'type' => 'server',
                    'id' => $server->id,
                    'name' => $server->name,
                    'ip_address' => $server->ip_address,
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        $this->blacklist = $this->blacklist();

        $this->honeypots = Honeypot::all()
            ->map(function (Honeypot $honeypot) {
                $counts = $this->honeypotEventCounts($honeypot);
                $max = collect($counts)->max(fn($count) => $count['human_or_targeted'] + $count['not_human_or_targeted']);
                $sum = collect($counts)->sum(fn($count) => $count['human_or_targeted'] + $count['not_human_or_targeted']);
                return [
                    'name' => $honeypot->dns,
                    'type' => $honeypot->cloud_sensor,
                    'counts' => $counts,
                    'max' => $max,
                    'sum' => $sum,
                ];
            })
            ->sortBy(fn(array $honeypot) => [-$honeypot['sum'], $honeypot['name']])
            ->values()
            ->take(3)
            ->toArray();

        $this->mostRecentHoneypotEvents = Honeypot::all()
            ->map(function (Honeypot $honeypot) {
                $events = $this->mostRecentHoneypotEvents($honeypot);
                return [
                    'name' => $honeypot->dns,
                    'events' => $events,
                ];
            })
            ->groupBy('name')
            ->map(fn($group) => $group->first())
            ->take(3)
            ->toArray();
    }

    public function render(): View|Closure|string
    {
        return view('components.timeline');
    }

    private function conversations(User $user): array
    {
        return Conversation::query()
            ->where('created_by', $user->id)
            ->where('dom', '!=', '[]')
            ->get()
            ->map(function (Conversation $conversation) use ($user) {

                $timestamp = $conversation->created_at->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newConversation($user, $conversation),
                ];
            })
            ->toArray();
    }

    private function assets(User $user): array
    {
        return Asset::query()
            ->when($this->assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->map(function (Asset $asset) use ($user) {

                $timestamp = $asset->created_at->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newAsset($user, $asset),
                    '_asset' => $asset,
                ];
            })
            ->toArray();
    }

    private function leaks(User $user): array
    {
        $now = Carbon::now()->utc()->subDays(30);
        $leaks = TimelineItem::fetchLeaks($user->id, $now, null, 0);

        if ($leaks->isEmpty()) {

            $tlds = "'" . Asset::all()
                    ->map(fn(Asset $asset) => $asset->tld())
                    ->filter(fn(?string $tld) => !empty($tld))
                    ->unique()
                    ->join("','") . "'";

            if ($tlds === "''") {
                $leaks = collect();
            } else {
                $query = "SELECT DISTINCT lower(concat(login, '@', login_email_domain)) AS email, concat(url_scheme, '://', url_subdomain, '.', url_domain) AS website, password FROM dumps_login_email_domain WHERE login_email_domain IN ({$tlds}) ORDER BY email, website ASC";

                Log::debug($query);

                $output = JosianneClient::executeQuery($query);
                $leaks = collect(explode("\n", $output))
                    ->filter(fn(string $line) => !empty($line) && $line !== 'ok')
                    ->map(function (string $line) {
                        return [
                            'email' => Str::trim(Str::before($line, "\t")),
                            'website' => Str::trim(Str::between($line, "\t", "\t")),
                            'password' => $this->maskPassword(Str::trim(Str::afterLast($line, "\t"))),
                        ];
                    })
                    ->map(function (array $credentials) {
                        // if (preg_match("/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|(([^\s()<>]+|(([^\s()<>]+)))*))+(?:(([^\s()<>]+|(([^\s()<>]+)))*)|[^\s`!()[]{};:'\".,<>?«»“”‘’]))/", $credentials['website'])) {
                        if (filter_var($credentials['website'], FILTER_VALIDATE_URL)) {
                            return $credentials;
                        }
                        return [
                            'email' => $credentials['email'],
                            'website' => '',
                            'password' => $credentials['password'],
                        ];
                    })
                    ->unique(fn(array $credentials) => $credentials['email'] . $credentials['website'] . $credentials['password']);
            }
            if (count($leaks) > 0) {

                // Get previous leaks
                $leaksPrev = TimelineItem::fetchLeaks($user->id, null, $now, 0)
                    ->flatMap(fn(TimelineItem $item) => json_decode($item->attributes()['credentials']));

                $leaks = $leaks->filter(function (array $leak) use ($leaksPrev) {
                    return !$leaksPrev->contains(function (object $leakPrev) use ($leak) {
                        return $leakPrev->email === $leak['email'] &&
                            $leakPrev->website === $leak['website'] &&
                            $leakPrev->password === $leak['password'];
                    });
                });

                // Only add the new leaks
                if (count($leaks) > 0) {
                    $leaks->chunk(10)->each(fn(\Illuminate\Support\Collection $leaksChunk) => TimelineItem::createLeak($user, $leaksChunk->values()->toArray()));
                }
            }
        }
        return TimelineItem::fetchLeaks($user->id, null, null, 0)
            ->map(function (TimelineItem $item) use ($user) {

                $timestamp = $item->timestamp->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newLeak($user, $item),
                ];
            })
            ->toArray();
    }

    private function notes(User $user): array
    {
        return TimelineItem::fetchNotes($user->id, null, null, 0)
            ->map(function (TimelineItem $item) use ($user) {

                $timestamp = $item->timestamp->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newNote($user, $item),
                ];
            })
            ->toArray();
    }

    private function scans(User $user): array
    {
        return Asset::where('is_monitored', true)
            ->when($this->assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->filter(fn(Asset $asset) => $asset->scanInProgress()->isNotEmpty() || ($asset->scanCompleted()->isNotEmpty() && $asset->alerts()->count() <= 0))
            ->map(function (Asset $asset) use ($user) {

                // Load the asset's scans
                $scansInProgress = $asset->scanInProgress();

                if ($scansInProgress->isNotEmpty()) {
                    $scans = $scansInProgress;
                } else {
                    $scans = $asset->scanCompleted();
                }

                /** @var Carbon $portsScanBeginsAt */
                $portsScanBeginsAt = $scans
                    ->filter(fn(Scan $scan) => $scan->ports_scan_begins_at != null)
                    ->sortBy('ports_scan_begins_at')
                    ->first()?->ports_scan_begins_at;

                /** @var ?Carbon $portsScanEndsAt */
                $portsScanEndsAt = $scans->contains(fn(Scan $scan) => $scan->ports_scan_ends_at == null) ?
                    null :
                    $scans->sortBy('ports_scan_ends_at')->last()?->ports_scan_ends_at;

                /** @var ?Carbon $vulnsScanBeginsAt */
                $vulnsScanBeginsAt = $scans
                    ->filter(fn(Scan $scan) => $scan->vulns_scan_ends_at != null)
                    ->sortBy('vulns_scan_begins_at')
                    ->first()?->vulns_scan_begins_at;

                /** @var ?Carbon $vulnsScanEndsAt */
                $vulnsScanEndsAt = $scans->contains(fn(Scan $scan) => $scan->vulns_scan_ends_at == null) ?
                    null :
                    $scans->sortBy('vulns_scan_ends_at')->last()?->vulns_scan_ends_at;

                // Load the number of remaining steps
                $nbRunningScans = $scans->filter(fn(Scan $scan) => $scan->vulns_scan_ends_at == null)->count();
                $nbCompletedScans = $scans->filter(fn(Scan $scan) => $scan->vulns_scan_ends_at != null)->count();

                $timestamp = $portsScanBeginsAt->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newScan($user, $asset, $portsScanBeginsAt, $portsScanEndsAt, $vulnsScanBeginsAt, $vulnsScanEndsAt, $nbRunningScans, $nbCompletedScans),
                    '_asset' => $asset,
                ];
            })
            ->toArray();
    }

    private function vulnerabilities(User $user): array
    {
        return Asset::where('is_monitored', true)
            ->when($this->assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
            ->filter(fn(Alert $alert) => $alert->is_hidden === 0)
            ->map(function (Alert $alert) use ($user) {

                $timestamp = $alert->updated_at->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');
                $asset = $alert->asset();

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newVulnerability($user, $alert, $asset, $alert->port()),
                    '_asset' => $asset,
                ];
            })
            ->toArray();
    }

    private function events(User $user): array
    {
        $cutOffTime = Carbon::now()->startOfDay()->subDay();
        $servers = YnhServer::query()->when($this->serverId, fn($query, $serverId) => $query->where('id', $serverId))->get();
        return Messages::get($servers, $cutOffTime, [
            Messages::AUTHENTICATION_AND_SSH_ACTIVITY,
            Messages::SERVICES_AND_SCHEDULED_TASKS,
            Messages::SHELL_HISTORY_AND_ROOT_COMMANDS,
            Messages::PACKAGES,
            Messages::USERS_AND_GROUPS,
        ])
            ->map(function (array $msg) use ($user) {

                $timestamp = $msg['timestamp'];
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newEvent($user, $msg),
                    '_server' => Cache::remember("server_{$msg['ip']}_{$msg['server']}", now()->addHours(3), function () use ($msg) {
                        return YnhServer::where('name', $msg['server'])
                            ->where('ip_address', $msg['ip'])
                            ->first();
                    }),
                ];
            })
            ->toArray();
    }

    private function maskPassword(string $password, int $size = 3): string
    {
        if (Str::length($password) <= 2) {
            return Str::repeat('*', Str::length($password));
        }
        if (Str::length($password) <= 2 * $size) {
            return $this->maskPassword($password, 1);
        }
        return Str::substr($password, 0, $size) . Str::repeat('*', Str::length($password) - 2 * $size) . Str::substr($password, -$size, $size);
    }

    private function blacklist(?int $attackerId = null)
    {
        /** @var array $ips */
        $ips = config('towerify.adversarymeter.ip_addresses');
        $events = HoneypotEvent::select(
            'am_honeypots_events.ip',
            DB::raw('MIN(am_honeypots_events.timestamp) AS first_contact'),
            DB::raw('MAX(am_honeypots_events.timestamp) AS last_contact'),
            DB::raw("MAX(am_honeypots_events.hosting_service_description) AS isp_name"),
            DB::raw("MAX(am_honeypots_events.hosting_service_country_code) AS country_code"),
        )
            ->whereIn('honeypot_id', Honeypot::all()->pluck('id'))
            ->whereNotIn('am_honeypots_events.ip', $ips)
            ->join('am_attackers', 'am_attackers.id', '=', 'am_honeypots_events.attacker_id');
        if ($attackerId) {
            $events->where('am_honeypots_events.attacker_id', $attackerId);
        }
        return $events->groupBy('ip')->distinct()->get()->toArray();
    }

    private function honeypotEventCounts(Honeypot $honeypot): array
    {
        $cutOffTime = Carbon::now()->startOfDay()->subMonth();
        return HoneypotEvent::select(
            DB::raw("DATE_FORMAT(timestamp, '%Y-%m-%d') AS date"),
            DB::raw("SUM(CASE WHEN human = 1 OR targeted = 1 THEN 1 ELSE 0 END) AS human_or_targeted"),
            DB::raw("SUM(CASE WHEN human = 0 AND targeted = 0 THEN 1 ELSE 0 END) AS not_human_or_targeted")
        )
            ->where('timestamp', '>=', $cutOffTime)
            ->where('honeypot_id', $honeypot->id)
            ->groupBy('date')
            ->orderBy('date', 'desc') // keep only the most recent ones
            ->limit(10)
            ->get()
            ->sortBy('date') // most recent date at the end
            ->toArray();
    }

    private function mostRecentHoneypotEvents(Honeypot $honeypot): array
    {
        /** @var array $ips */
        $ips = config('towerify.adversarymeter.ip_addresses');
        return HoneypotEvent::select(
            'am_honeypots_events.*',
            DB::raw("CASE WHEN am_attackers.name IS NULL THEN '-' ELSE am_attackers.name END AS internal_name"),
            DB::raw("CASE WHEN am_attackers.id IS NULL THEN '-' ELSE am_attackers.id END AS attacker_id"),
        )
            ->where('honeypot_id', $honeypot->id)
            ->whereNotIn('ip', $ips)
            ->leftJoin('am_attackers', 'am_attackers.id', '=', 'am_honeypots_events.attacker_id')
            ->orderBy('timestamp', 'desc')
            ->limit(5)
            ->get()
            ->map(function (HoneypotEvent $event) {
                return [
                    'timestamp' => $event->timestamp->utc()->format('Y-m-d H:i:s'),
                    'event_type' => $event->event,
                    'event_details' => $event->details,
                    'attacker_ip' => $event->ip,
                    'attacker_name' => $event->internal_name,
                    'attacker_id' => $event->attacker_id,
                ];
            })
            ->toArray();
    }
}
