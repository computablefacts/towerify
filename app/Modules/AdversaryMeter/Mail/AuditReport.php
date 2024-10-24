<?php

namespace App\Modules\AdversaryMeter\Mail;

use App\Models\YnhOsquery;
use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditReport extends Mailable
{
    use Queueable, SerializesModels;

    private Collection $alertsHigh;
    private Collection $alertsMedium;
    private Collection $alertsLow;
    private Collection $assetsMonitored;
    private Collection $assetsNotMonitored;
    private Collection $assetsDiscovered;
    private Collection $events;
    private Collection $metrics;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Collection $events, Collection $metrics, Collection $alertsHigh, Collection $alertsMedium, Collection $alertsLow, Collection $assetsMonitored, Collection $assetsNotMonitored, Collection $assetsDiscovered)
    {
        $this->events = $events;
        $this->metrics = $metrics;
        $this->alertsHigh = $alertsHigh;
        $this->alertsMedium = $alertsMedium;
        $this->alertsLow = $alertsLow;
        $this->assetsMonitored = $assetsMonitored;
        $this->assetsNotMonitored = $assetsNotMonitored;
        $this->assetsDiscovered = $assetsDiscovered;
    }

    public static function create(): array
    {
        $serverIds = YnhServer::forUser(Auth::user())->pluck('id');
        $cutOffTime = Carbon::now()->subDay();
        $alerts = Asset::where('is_monitored', true)
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
            ->filter(fn(Alert $alert) => $alert->is_hidden === 0);
        $alertsHigh = $alerts->filter(fn(Alert $alert) => $alert->level === 'High');
        $alertsMedium = $alerts->filter(fn(Alert $alert) => $alert->level === 'Medium');
        $alertsLow = $alerts->filter(fn(Alert $alert) => $alert->level === 'Low');
        $assetsMonitored = Asset::where('is_monitored', true)->orderBy('asset')->get();
        $assetsNotMonitored = Asset::where('is_monitored', false)->orderBy('asset')->get();
        $assetsDiscovered = Asset::where('created_at', '>=', $cutOffTime)->orderBy('asset')->get();
        $events = YnhOsquery::where('calendar_time', '>=', $cutOffTime)
            ->whereIn('name', [
                'authorized_keys',
                'last',
                'users',
                'suid_bin',
                'ld_preload',
                'kernel_modules',
                'crontab',
                'etc_hosts',
                'mounts',
            ])
            ->whereIn('ynh_server_id', $serverIds)
            ->orderBy('calendar_time', 'desc')
            ->get()
            ->map(function (YnhOsquery $event) {
                if ($event->name === 'authorized_keys') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Une clef SSH a été ajoutée au trousseau de l'utilisateur {$event->columns['username']}.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Une clef SSH a été supprimée du trousseau de l'utilisateur {$event->columns['username']}.",
                        ];
                    }
                } elseif ($event->name === 'last') {
                    if ($event->action === 'added') {
                        if ($event->columns['type_name'] === 'user-process') {
                            return [
                                'id' => $event->id,
                                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                                'server' => $event->server->name,
                                'message' => "L'utilisateur {$event->columns['username']} s'est connecté au serveur."
                            ];
                        }
                    }
                } elseif ($event->name === 'users') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "L'utilisateur {$event->columns['username']} a été créé.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "L'utilisateur {$event->columns['username']} a été supprimé.",
                        ];
                    }
                } elseif ($event->name === 'suid_bin') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Les privilèges du binaire {$event->columns['path']} ont été élevés.",
                        ];
                    }
                } elseif ($event->name === 'ld_preload') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Le binaire {$event->columns['value']} a été ajouté à la variable d'environnement LD_PRELOAD.",
                        ];
                    }
                } elseif ($event->name === 'kernel_modules') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Le module {$event->columns['name']} a été ajouté au noyau.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Le module {$event->columns['name']} a été enlevé du noyau.",
                        ];
                    }
                } elseif ($event->name === 'crontab') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Une tâche planifiée a été ajoutée: {$event->columns['command']}",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Une tâche planifiée a été supprimée: {$event->columns['command']}",
                        ];
                    }
                } elseif ($event->name === 'etc_hosts') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Le traffic réseau vers {$event->columns['hostnames']} est maintenant redirigé vers {$event->columns['address']}.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'message' => "Le traffic réseau vers {$event->columns['hostnames']} n'est maintenant plus redirigé vers {$event->columns['address']}.",
                        ];
                    }
                } elseif ($event->name === 'mounts') {
                    if (Str::startsWith($event->columns['path'], '/var/lib/docker/') && $event->columns['type'] === 'overlay') { // Docker-generated 'mounts' events
                        if ($event->action === 'added') {
                            return [
                                'id' => $event->id,
                                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                                'server' => $event->server->name,
                                'message' => "Le répertoire {$event->columns['path']} pointe maintenant vers un système de fichiers de type {$event->columns['type']}.",
                            ];
                        } elseif ($event->action === 'removed') {
                            return [
                                'id' => $event->id,
                                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                                'server' => $event->server->name,
                                'message' => "Le répertoire {$event->columns['path']} ne pointe maintenant plus vers un système de fichiers de type {$event->columns['type']}.",
                            ];
                        }
                    }
                }
                return [];
            })
            ->filter(fn(array $event) => count($event) >= 1);
        $metrics = $serverIds->map(function (int $serverId) use ($cutOffTime) {

            /** @var YnhOsquery $metric */
            $metric = YnhOsquery::where('calendar_time', '>=', $cutOffTime)
                ->where('name', 'disk_available_snapshot')
                ->where('ynh_server_id', $serverId)
                ->orderBy('calendar_time', 'desc')
                ->first();

            if ($metric && $metric->columns['%_available'] <= 20) {
                return [
                    'timestamp' => $metric->calendar_time->format('Y-m-d H:i:s'),
                    'server' => $metric->server->name,
                    'message' => "Il vous reste {$metric->columns['%_available']}% d'espace disque disponible, soit {$metric->columns['space_left_gb']} Gb.",
                ];
            }
            return [];
        })
            ->filter(fn(array $event) => count($event) >= 1);

        return [
            'is_empty' => $events->count() <= 0 &&
                $metrics->count() <= 0 &&
                $alerts->count() <= 0 &&
                $assetsMonitored->count() <= 0 &&
                $assetsNotMonitored->count() <= 0 &&
                $assetsDiscovered->count() <= 0,
            'report' => new AuditReport($events, $metrics, $alertsHigh, $alertsMedium, $alertsLow, $assetsMonitored, $assetsNotMonitored, $assetsDiscovered),
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $events = '';
        if ($this->events->count() > 0) {
            if ($this->events->count() === 1) {
                $events .= "{$this->events->count()} évènement anormal";
            } else {
                $events .= "{$this->events->count()} évènements anormaux";
            }
        }
        if ($this->metrics->count() > 0) {
            if (!empty($events)) {
                $events .= ", ";
            }
            if ($this->metrics->count() === 1) {
                $events .= "{$this->metrics->count()} métrique anormale";
            } else {
                $events .= "{$this->metrics->count()} métriques anormales";
            }
        }
        if ($this->alertsHigh->count() > 0) {
            if (!empty($events)) {
                $events .= ", ";
            }
            if ($this->alertsHigh->count() === 1) {
                $events .= "{$this->alertsHigh->count()} vulnérabilité critique";
            } else {
                $events .= "{$this->alertsHigh->count()} vulnérabilités critiques";
            }
        }
        if ($this->assetsDiscovered->count() > 0) {
            if (!empty($events)) {
                $events .= ", ";
            }
            if ($this->assetsDiscovered->count() === 1) {
                $events .= "{$this->assetsDiscovered->count()} nouvel actif découvert";
            } else {
                $events .= "{$this->assetsDiscovered->count()} nouveaux actifs découverts";
            }
        }
        $events = empty($events) ? '' : "({$events})";
        return $this
            ->from('support@computablefacts.freshdesk.com', 'Support')
            ->subject("Cywise : Rapport d'audit {$events}")
            ->markdown('modules.adversary-meter.email.audit-report', [
                "events" => $this->events,
                "metrics" => $this->metrics,
                "alerts_high" => $this->alertsHigh,
                "alerts_medium" => $this->alertsMedium,
                "alerts_low" => $this->alertsLow,
                "assets_monitored" => $this->assetsMonitored,
                "assets_not_monitored" => $this->assetsNotMonitored,
                "assets_discovered" => $this->assetsDiscovered,
            ]);
    }
}
