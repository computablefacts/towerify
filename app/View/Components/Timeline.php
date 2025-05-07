<?php

namespace App\View\Components;

use App\Helpers\Messages;
use App\Models\Alert;
use App\Models\Asset;
use App\Models\Port;
use App\Models\PortTag;
use App\Models\TimelineItem;
use App\Models\YnhOsquery;
use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Timeline extends Component
{
    const string CATEGORY_ALL = 'all';
    const string CATEGORY_EVENTS = 'events';
    const string CATEGORY_ASSETS = 'assets';
    const string CATEGORY_NOTES = 'notes';
    const string CATEGORY_VULNERABILITIES = 'vulnerabilities';

    public string $todaySeparator;
    public array $messages;

    // Filters
    public array $assets;
    public int $assetId;
    public array $dates;
    public string $dateId;
    public int $minScore;
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

    public static function newEvent(User $user, YnhOsquery $event): string
    {
        $timestamp = $event->calendar_time->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        if ($event->score > 50) {
            $txtColor = "white";
            $bgColor = "#ff4d4d";
        } else {
            $txtColor = "white";
            $bgColor = "#ffaa00";
        }
        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-event', [
            'date' => $date,
            'time' => $time,
            'txtColor' => $txtColor,
            'bgColor' => $bgColor,
            'event' => $event,
        ])->render();
    }

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $params = request()->query();
        $this->assetId = (int)($params['asset_id'] ?? 0);
        $this->dateId = $params['date'] ?? '';
        $this->categoryId = $params['category'] ?? self::CATEGORY_ALL;
        $this->minScore = $params['min_score'] ?? 30;
        $this->todaySeparator = self::newSeparator(Carbon::now());
        $this->categories = [
            self::CATEGORY_ALL,
            self::CATEGORY_ASSETS,
            self::CATEGORY_EVENTS,
            self::CATEGORY_NOTES,
            self::CATEGORY_VULNERABILITIES,
        ];

        $messages = collect();

        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_ALL || $this->categoryId === self::CATEGORY_ASSETS) {
            $messages = $messages->concat($this->assets($user));
        }
        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_ALL || $this->categoryId === self::CATEGORY_EVENTS) {
            $messages = $messages->concat($this->events($user));
        }
        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_ALL || $this->categoryId === self::CATEGORY_NOTES) {
            $messages = $messages->concat($this->notes($user));
        }
        if (empty($this->categoryId) || $this->categoryId === self::CATEGORY_ALL || $this->categoryId === self::CATEGORY_VULNERABILITIES) {
            $messages = $messages->concat($this->vulnerabilities($user));
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
            ->map(function (array $event) {
                /** @var Asset $asset */
                $asset = $event['_asset'];
                return [
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
    }

    public function render(): View|Closure|string
    {
        return view('components.timeline');
    }

    private function assets(User $user): array
    {
        return Asset::query()
            ->when($this->assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->map(function (Asset $asset) use ($user) {

                $timestamp = $asset->created_at->format('Y-m-d H:i:s');
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

    private function notes(User $user): array
    {
        return TimelineItem::fetchItems($user->id, 'note', null, null, 0)
            ->map(function (TimelineItem $item) use ($user) {

                $timestamp = $item->timestamp->format('Y-m-d H:i:s');
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

    private function vulnerabilities(User $user): array
    {
        return Asset::where('is_monitored', true)
            ->when($this->assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
            ->filter(fn(Alert $alert) => $alert->is_hidden === 0)
            ->map(function (Alert $alert) use ($user) {

                $timestamp = $alert->updated_at->format('Y-m-d H:i:s');
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
        return YnhOsquery::select([
            'ynh_osquery.*',
            DB::raw('ynh_servers.name AS server_name'),
            'ynh_osquery_rules.score',
            'ynh_osquery_rules.comments',
        ])
            ->join('ynh_osquery_rules', 'ynh_osquery_rules.name', '=', 'ynh_osquery.name')
            ->join('ynh_servers', 'ynh_servers.id', '=', 'ynh_osquery.ynh_server_id')
            ->join('users', 'users.id', '=', 'ynh_servers.user_id')
            ->where('ynh_osquery.calendar_time', '>=', $cutOffTime)
            ->where('ynh_osquery_rules.enabled', true)
            ->where('ynh_osquery_rules.score', '>=', $this->minScore)
            ->where('users.tenant_id', Auth::user()->tenant_id)
            ->when(Auth::user()->tenant_id, fn($query, int $tenantId) => $query->where('users.tenant_id', $tenantId))
            ->when(Auth::user()->customer_id, fn($query, int $customerId) => $query->where('users.customer_id', $customerId))
            ->whereNotExists(function (Builder $query) {
                $query->select(DB::raw(1))
                    ->from('v_dismissed')
                    ->whereColumn('ynh_server_id', '=', 'ynh_osquery.ynh_server_id')
                    ->whereColumn('name', '=', 'ynh_osquery.name')
                    ->whereColumn('action', '=', 'ynh_osquery.action')
                    ->whereColumn('columns_uid', '=', 'ynh_osquery.columns_uid')
                    ->havingRaw('count(1) >=' . Messages::HIDE_AFTER_DISMISS_COUNT);
            })
            ->get()
            ->map(function (YnhOsquery $event) use ($user) {

                $timestamp = $event->calendar_time->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => self::newEvent($user, $event),
                ];
            })
            ->toArray();
    }
}
