<?php

namespace App\Http\Controllers\Iframes;

use App\Helpers\JosianneClient;
use App\Helpers\Messages;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Asset;
use App\Models\Conversation;
use App\Models\PortTag;
use App\Models\TimelineItem;
use App\Models\User;
use App\Models\YnhServer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public static function noteAndMemo(User $user, TimelineItem $item): array
    {
        $timestamp = $item->timestamp->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        return [
            'timestamp' => $timestamp,
            'date' => $date,
            'time' => $time,
            'html' => \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._note', [
                'date' => $date,
                'time' => $time,
                'user' => $user,
                'note' => $item,
            ])->render(),
        ];
    }

    public function __invoke(Request $request): View
    {
        $params = $request->validate([
            'status' => ['nullable', 'string', 'in:monitorable,monitored'],
            'level' => ['nullable', 'string', 'in:low,medium,high'],
        ]);
        $objects = last(explode('/', trim($request->path(), '/')));
        $items = match ($objects) {
            'assets' => $this->assets($params['status'] ?? null),
            'conversations' => $this->conversations(),
            'events' => $this->events(),
            'ioc' => $this->ioc(),
            'leaks' => $this->leaks(),
            'notes-and-memos' => $this->notesAndMemos(),
            'vulnerabilities' => $this->vulnerabilities($params['level'] ?? null),
            default => [],
        };
        return view('cywise.iframes.timeline', [
            'today_separator' => $this->separator(Carbon::now()),
            'items' => $items->sortByDesc('timestamp')
                ->groupBy(fn(array $event) => $event['date'])
                ->mapWithKeys(function ($events, $timestamp) {
                    return [
                        $timestamp => collect($events)
                            ->sortByDesc('time')
                            ->groupBy(fn(array $event) => $event['time'])
                    ];
                })
                ->toArray(),
        ]);
    }

    private function separator(Carbon $date): string
    {
        $timestamp = $date->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');

        return Str::replace("\n", '', \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._separator', [
            'date' => $date,
        ])->render());
    }

    private function assets(?string $status = null, ?int $assetId = null): Collection
    {
        return Asset::query()
            ->when($status, function ($query, $status) {
                if ($status === 'monitorable') {
                    $query->where('is_monitored', false);
                } else if ($status === 'monitored') {
                    $query->where('is_monitored', true);
                }
            })
            ->when($assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->map(function (Asset $asset) {

                $timestamp = $asset->created_at->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._asset', [
                        'date' => $date,
                        'time' => $time,
                        'asset' => $asset,
                    ])->render(),
                    '_asset' => $asset,
                ];
            });
    }

    private function conversations(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

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
                    'html' => \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._conversation', [
                        'date' => $date,
                        'time' => $time,
                        'conversation' => $conversation,
                    ])->render(),
                ];
            });
    }

    private function events(?int $serverId = null): Collection
    {
        $cutOffTime = Carbon::now()->startOfDay()->subDay();
        $servers = YnhServer::query()
            ->when($serverId, fn($query, $serverId) => $query->where('id', $serverId))
            ->get();

        return Messages::get($servers, $cutOffTime, [
            Messages::AUTHENTICATION_AND_SSH_ACTIVITY,
            // Messages::SERVICES_AND_SCHEDULED_TASKS,
            Messages::SHELL_HISTORY_AND_ROOT_COMMANDS,
            Messages::PACKAGES,
            Messages::USERS_AND_GROUPS,
        ])
            ->map(function (array $msg) {

                $timestamp = $msg['timestamp'];
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._event', [
                        'date' => $date,
                        'time' => $time,
                        'msg' => $msg,
                    ])->render(),
                    '_server' => Cache::remember("server_{$msg['ip']}_{$msg['server']}", now()->addHours(3), function () use ($msg) {
                        return YnhServer::where('name', $msg['server'])
                            ->where('ip_address', $msg['ip'])
                            ->first();
                    }),
                ];
            });
    }

    private function ioc(): Collection
    {
        return collect();
    }

    private function leaks(): Collection
    {
        /** @var User $user */
        $user = Auth::user();
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
                    $leaks->chunk(10)->each(fn(Collection $leaksChunk) => TimelineItem::createLeak($user, $leaksChunk->values()->toArray()));
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
                    'html' => \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._leak', [
                        'date' => $date,
                        'time' => $time,
                        'user' => $user,
                        'leak' => $item,
                    ])->render(),
                ];
            });
    }

    private function notesAndMemos(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return TimelineItem::fetchNotes($user->id, null, null, 0)
            ->map(fn(TimelineItem $item) => self::noteAndMemo($user, $item));
    }

    private function vulnerabilities(?string $level = null, ?int $assetId = null): Collection
    {
        return $this->alerts($level, $assetId)
            ->map(function (Alert $alert) {

                $timestamp = $alert->updated_at->utc()->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');
                $asset = $alert->asset();
                $port = $alert->port();

                if ($alert->level === 'High') {
                    $txtColor = "white";
                    $bgColor = "var(--c-red)";
                    $level = "(" . __("high") . ")";
                } else if ($alert->level === 'Medium') {
                    $txtColor = "white";
                    $bgColor = "var(--c-orange-light)";
                    $level = "(" . __("medium") . ")";
                } else if ($alert->level === 'Low') {
                    $txtColor = "white";
                    $bgColor = "var(--c-green)";
                    $level = "(" . __("low") . ")";
                } else {
                    $txtColor = "var(--c-grey-400)";
                    $bgColor = "var(--c-grey-100)";
                    $level = "(" . __("inconnue") . ")";
                }

                $tags = "<div><span class='lozenge new' style='font-size: 0.8rem;margin-top: 3px;'>" . $port
                        ->tags()
                        ->get()
                        ->map(fn(PortTag $tag) => Str::lower($tag->tag))
                        ->join("</span>&nbsp;<span class='lozenge new' style='font-size: 0.8rem;margin-top: 3px;'>") . "</span></div>";

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => \Illuminate\Support\Facades\View::make('cywise.iframes.timeline._vulnerability', [
                        'date' => $date,
                        'time' => $time,
                        'txtColor' => $txtColor,
                        'bgColor' => $bgColor,
                        'level' => $level,
                        'tags' => $tags,
                        'alert' => $alert,
                        'asset' => $asset,
                        'port' => $port,
                    ])->render(),
                    '_asset' => $asset,
                ];
            });
    }

    private function alerts(?string $level = null, ?int $assetId = null): Collection
    {
        return Asset::where('is_monitored', true)
            ->when($assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->alerts()->when($level, function ($query, $level) {
                if ($level === 'high') {
                    $query->where('level', 'High');
                } else if ($level === 'medium') {
                    $query->where('level', 'Medium');
                } else if ($level === 'low') {
                    $query->where('level', 'Low');
                }
            })->get())
            ->filter(fn(Alert $alert) => $alert->is_hidden === 0);
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
}
