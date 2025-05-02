<?php

namespace App\View\Components;

use App\Helpers\Messages;
use App\Models\Alert;
use App\Models\Asset;
use App\Models\YnhServer;
use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        /* $tmp = collect([
            [
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'message' => 'L\'utilisateur ubuntu s\'est connecté au serveur 127.0.0.1',
            ], [
                'timestamp' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'message' => 'L\'utilisateur ubuntu s\'est connecté au serveur 127.0.0.1',
            ], [
                'timestamp' => Carbon::now()->subDay()->subHour()->format('Y-m-d H:i:s'),
                'message' => 'L\'utilisateur ubuntu s\'est connecté au serveur 127.0.0.1',
            ]
        ]); */

        $this->messages = Messages::get($servers, $cutOffTime)
            ->map(function (array $event) {
                $event['svg'] = '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-server"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M7 8l0 .01" /><path d="M7 16l0 .01" /></svg>';
                return $event;
            })
            ->concat(
                Asset::query()
                    ->get()
                    ->map(function (Asset $asset) {
                        return [
                            'timestamp' => $asset->created_at->format('Y-m-d H:i:s'),
                            'message' => "{$asset->createdBy()->name} a ajouté l'actif {$asset->asset}",
                            'txt-color' => "white",
                            'bg-color' => "var(--c-blue-500)",
                            'svg' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-world-www"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 7a9 9 0 0 0 -7.5 -4a8.991 8.991 0 0 0 -7.484 4" /><path d="M11.5 3a16.989 16.989 0 0 0 -1.826 4" /><path d="M12.5 3a16.989 16.989 0 0 1 1.828 4" /><path d="M19.5 17a9 9 0 0 1 -7.5 4a8.991 8.991 0 0 1 -7.484 -4" /><path d="M11.5 21a16.989 16.989 0 0 1 -1.826 -4" /><path d="M12.5 21a16.989 16.989 0 0 0 1.828 -4" /><path d="M2 10l1 4l1.5 -4l1.5 4l1 -4" /><path d="M17 10l1 4l1.5 -4l1.5 4l1 -4" /><path d="M9.5 10l1 4l1.5 -4l1.5 4l1 -4" /></svg>',
                        ];
                    })
                    ->toArray()
            )
            ->concat(
                Asset::where('is_monitored', true)
                    ->get()
                    ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
                    ->filter(fn(Alert $alert) => $alert->is_hidden === 0)
                    ->map(function (Alert $alert) {

                        $vulnerability = Cache::remember("translate_vulnerability_{$alert->id}", $alert->created_at->addDays(10), function () use ($alert) {
                            // $result = ApiUtils2::translate($alert->vulnerability);
                            // if ($result ['error'] !== false) {
                            return $alert->vulnerability;
                            // }
                            // return $result['response'];
                        });

                        $remediation = Cache::remember("translate_remediation_{$alert->id}", $alert->created_at->addDays(10), function () use ($alert) {
                            // $result = ApiUtils2::translate($alert->remediation);
                            // if ($result ['error'] !== false) {
                            return $alert->remediation;
                            // }
                            // return $result['response'];
                        });

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

                        if (empty($alert->cve_id)) {
                            $cve = "";
                        } else {
                            $cve = "<p><b>Note.</b> Cette vulnérabilité a pour identifiant <a href=\"https://nvd.nist.gov/vuln/detail/{$alert->cve_id}\">{$alert->cve_id}</a>.</p>";
                        }

                        $message = $alert->cve_id ? "{$alert->cve_id} - {$alert->title} $level" : "{$alert->title} $level";

                        return [
                            'timestamp' => $alert->updated_at->format('Y-m-d H:i:s'),
                            'message' => $message,
                            'txt-color' => $txtColor,
                            'bg-color' => $bgColor,
                            'svg' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-alert-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 16h.01" /></svg>',
                            'comment' => "
                                <p><b>Actif.</b> L'actif concerné est {$alert->asset()?->asset} pointant vers le serveur 
                                {$alert->port()?->ip}. Le port {$alert->port()?->port} de ce serveur est ouvert et expose un service 
                                {$alert->port()?->service} ({$alert->port()?->product}).</p>
                                <p><b>Problème</b>. {$vulnerability}</p>
                                <p><b>Solution</b>. {$remediation}</p>
                                {$cve}
                            ",
                        ];
                    })
                    ->toArray()
            )
            ->sortByDesc('timestamp')
            ->groupBy(fn(array $event) => Str::before($event['timestamp'], ' '))
            ->mapWithKeys(function ($events, $timestamp) {
                return [
                    $timestamp => collect($events)
                        ->sortByDesc('timestamp')
                        ->groupBy(fn(array $event) => Str::beforeLast(Str::after($event['timestamp'], ' '), ':'))
                ];
            })
            ->toArray();
    }

    public function render(): View|Closure|string
    {
        return view('components.timeline');
    }
}
