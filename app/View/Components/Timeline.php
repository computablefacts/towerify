<?php

namespace App\View\Components;

use App\Models\Alert;
use App\Models\Asset;
use App\Models\PortTag;
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
    public array $messages;

    public function __construct()
    {
        /** @var User $user */
        $user = Auth::user();
        $servers = YnhServer::forUser($user);
        $cutOffTime = Carbon::now()->startOfDay()->subDays(3);

        $this->messages = Asset::query()
            ->get()
            ->map(function (Asset $asset) {
                $timestamp = $asset->created_at->format('Y-m-d H:i:s');
                $date = Str::before($timestamp, ' ');
                $time = Str::beforeLast(Str::after($timestamp, ' '), ':');
                return [
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'time' => $time,
                    'html' => \Illuminate\Support\Facades\View::make('cywise._timeline-item-asset', [
                        'date' => $date,
                        'time' => $time,
                        'title' => "<a href='#'>{$asset->createdBy()->name}</a> a ajouté l'actif <a href='#'>{$asset->asset}</a>",
                    ])->render(),
                ];
            })
            ->concat(
                Asset::where('is_monitored', true)
                    ->get()
                    ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
                    ->filter(fn(Alert $alert) => $alert->is_hidden === 0)
                    ->map(function (Alert $alert) {

                        $timestamp = $alert->updated_at->format('Y-m-d H:i:s');
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

                        $title = empty($alert->cve_id) ? "<a href='#'>{$alert->asset()?->asset}</a> - {$alert->title} $level" : "<a href='#'>{$alert->asset()?->asset}</a> - {$alert->cve_id} / {$alert->title} $level";
                        $asset = "<p><b>Actif.</b> L'actif concerné est {$alert->asset()?->asset} pointant vers le serveur {$alert->port()?->ip}. Le port {$alert->port()?->port} de ce serveur est ouvert et expose un service {$alert->port()?->service} ({$alert->port()?->product}).</p>";
                        $vulnerability = "<p><b>Problème</b>. {$alert->vulnerability}</p>";
                        $remediation = "<p><b>Solution</b>. {$alert->remediation}</p>";
                        $cve = empty($alert->cve_id) ? "" : "<p><b>Note.</b> Cette vulnérabilité a pour identifiant <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a>.</p>";
                        $tags = "<div><span class='lozenge new'>" . $alert->port()
                                ->tags()
                                ->get()
                                ->map(fn(PortTag $tag) => Str::lower($tag->tag))
                                ->join("</span>&nbsp;<span class='lozenge new'>") . "</span><div>";

                        return [
                            'timestamp' => $alert->updated_at->format('Y-m-d H:i:s'),
                            'date' => $date,
                            'time' => $time,
                            'html' => \Illuminate\Support\Facades\View::make('cywise._timeline-item-vulnerability', [
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
                            ])->render(),
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
