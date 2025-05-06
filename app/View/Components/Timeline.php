<?php

namespace App\View\Components;

use App\Models\Alert;
use App\Models\Asset;
use App\Models\PortTag;
use App\Models\TimelineItem;
use App\Models\YnhServer;
use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Timeline extends Component
{
    public string $todaySeparator;
    public array $assets;
    public array $messages;
    public int $assetId;

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
        $attributes = $item->attributes();

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-note', [
            'date' => $date,
            'time' => $time,
            'title' => "<a href='#'>{$user->name}</a> a créé une <a href='#'>note</a>",
            'note' => $attributes['content'] ?? '',
            'noteId' => $item->id,
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
            'title' => "<a id='aid-{$asset->id}' href='#'>{$asset->createdBy()->name}</a> a ajouté l'actif <a href='#'>{$asset->asset}</a>",
            'assetId' => $asset->id,
            'isMonitored' => $asset->is_monitored,
        ])->render();
    }

    public static function newVulnerability(User $user, Alert $alert): string
    {
        $timestamp = $alert->updated_at->utc()->format('Y-m-d H:i:s');
        $date = Str::before($timestamp, ' ');
        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

        $txtColor = "var(--c-grey-400)";
        $bgColor = "rgba(125, 188, 255, 0.6)";
        $level = "(niveau inconnu)";

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
        }

        $title = empty($alert->cve_id) ? "<a href='#aid-{$alert->asset()?->id}'>{$alert->asset()?->asset}</a> - {$alert->title} $level" : "<a href='#'>{$alert->asset()?->asset}</a> - {$alert->cve_id} / {$alert->title} $level";
        $asset = "<p><b>Actif.</b> L'actif concerné est {$alert->asset()?->asset} pointant vers le serveur {$alert->port()?->ip}. Le port {$alert->port()?->port} de ce serveur est ouvert et expose un service {$alert->port()?->service} ({$alert->port()?->product}).</p>";
        $vulnerability = "<p><b>Problème</b>. {$alert->vulnerability}</p>";
        $remediation = "<p><b>Solution</b>. {$alert->remediation}</p>";
        $cve = empty($alert->cve_id) ? "" : "<p><b>Note.</b> Cette vulnérabilité a pour identifiant <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a>.</p>";
        $tags = "<div><span class='lozenge new'>" . $alert->port()
                ->tags()
                ->get()
                ->map(fn(PortTag $tag) => Str::lower($tag->tag))
                ->join("</span>&nbsp;<span class='lozenge new' style='font-size: 0.8rem;'>") . "</span></div>";

        return \Illuminate\Support\Facades\View::make('cywise._timeline-item-vulnerability', [
            'date' => $date,
            'time' => $time,
            'txtColor' => $txtColor,
            'bgColor' => $bgColor,
            'title' => $title,
            'asset' => $asset,
            'vulnerability' => $vulnerability,
            'remediation' => $remediation,
            'cve' => $cve,
            'tags' => $tags,
            'assetId' => $alert->asset()?->id,
            'filterByUid' => $alert->uid,
            'filterByType' => $alert->type,
            'filterByTitle' => $alert->title,
        ])->render();
    }

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $servers = YnhServer::forUser($user);
        $cutOffTime = Carbon::now()->startOfDay()->subDays(3);

        $params = request()->query();
        $this->assetId = (int)($params['asset_id'] ?? 0);

        $this->todaySeparator = self::newSeparator(Carbon::now());

        $this->assets = Asset::query()
            ->orderBy('asset')
            ->get()
            ->map(fn(Asset $asset) => [
                'id' => $asset->id,
                'name' => $asset->asset,
                'high' => $asset->alerts()->where('level', 'High')->count(),
                'medium' => $asset->alerts()->where('level', 'Medium')->count(),
                'low' => $asset->alerts()->where('level', 'Low')->count(),
            ])
            ->toArray();

        $this->messages = Asset::query()
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
                ];
            })
            ->concat(
                TimelineItem::fetchItems($user->id, 'note', null, null, 0)
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
            )
            ->concat(
                Asset::where('is_monitored', true)
                    ->when($this->assetId, fn($query, $assetId) => $query->where('id', $assetId))
                    ->get()
                    ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
                    ->filter(fn(Alert $alert) => $alert->is_hidden === 0)
                    ->map(function (Alert $alert) use ($user) {

                        $timestamp = $alert->updated_at->format('Y-m-d H:i:s');
                        $date = Str::before($timestamp, ' ');
                        $time = Str::beforeLast(Str::after($timestamp, ' '), ':');

                        return [
                            'timestamp' => $timestamp,
                            'date' => $date,
                            'time' => $time,
                            'html' => self::newVulnerability($user, $alert),
                        ];
                    })
                    ->toArray()
            )
            ->sortByDesc('timestamp')
            ->groupBy(fn(array $event) => $event['date'])
            ->mapWithKeys(function ($events, $timestamp) {
                return [
                    $timestamp => collect($events)->sortByDesc('time')->groupBy(fn(array $event) => $event['time'])
                ];
            })
            ->toArray();
    }

    public function render(): View|Closure|string
    {
        return view('components.timeline');
    }
}
