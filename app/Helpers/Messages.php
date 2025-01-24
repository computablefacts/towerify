<?php

namespace App\Helpers;

use App\Models\YnhCve;
use App\Models\YnhOsquery;

class Messages
{
    public static function get(YnhOsquery $event)
    {
        return match ($event->name) {
            'authorized_keys' => self::authorizedKeys($event),
            'last' => self::last($event),
            'users' => self::users($event),
            'suid_bin' => self::suidBin($event),
            'ld_preload' => self::ldPreload($event),
            'kernel_modules' => self::kernelModules($event),
            'crontab' => self::crontab($event),
            'etc_hosts' => self::etcHosts($event),
            'deb_packages' => self::debPackages($event),
            default => [],
        };
    }

    private static function authorizedKeys(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Une clef SSH a été ajoutée au trousseau de l'utilisateur {$event->columns['username']}.",
            ];
        } elseif ($event->action === 'removed') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Une clef SSH a été supprimée du trousseau de l'utilisateur {$event->columns['username']}.",
            ];
        }
        return [];
    }

    private static function last(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            if ($event->columns['type_name'] === 'user-process') {
                return [
                    'id' => $event->id,
                    'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                    'server' => $event->server->name,
                    'ip' => $event->server->ip(),
                    'message' => "L'utilisateur {$event->columns['username']} s'est connecté au serveur."
                ];
            }
        }
        return [];
    }

    private static function users(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "L'utilisateur {$event->columns['username']} a été créé.",
            ];
        } elseif ($event->action === 'removed') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "L'utilisateur {$event->columns['username']} a été supprimé.",
            ];
        }
        return [];
    }

    private static function suidBin(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Les privilèges du binaire {$event->columns['path']} ont été élevés.",
            ];
        }
        return [];
    }

    private static function ldPreload(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le binaire {$event->columns['value']} a été ajouté à la variable d'environnement LD_PRELOAD.",
            ];
        }
        return [];
    }

    private static function kernelModules(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le module {$event->columns['name']} a été ajouté au noyau.",
            ];
        } elseif ($event->action === 'removed') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le module {$event->columns['name']} a été enlevé du noyau.",
            ];
        }
        return [];
    }

    private static function crontab(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Une tâche planifiée a été ajoutée: {$event->columns['command']}",
            ];
        } elseif ($event->action === 'removed') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Une tâche planifiée a été supprimée: {$event->columns['command']}",
            ];
        }
        return [];
    }

    private static function etcHosts(YnhOsquery $event): array
    {
        if ($event->action === 'added') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le traffic réseau vers {$event->columns['hostnames']} est maintenant redirigé vers {$event->columns['address']}.",
            ];
        } elseif ($event->action === 'removed') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le traffic réseau vers {$event->columns['hostnames']} n'est maintenant plus redirigé vers {$event->columns['address']}.",
            ];
        }
        return [];
    }

    private static function debPackages(YnhOsquery $event): array
    {
        if ($event->action === 'added') {

            $osInfo = YnhOsquery::osInfos(collect([$event->server]))->first();

            if (!$osInfo) {
                $cves = '';
            } else {
                $cves = YnhCve::appCves($osInfo->os, $osInfo->codename, $event->columns['name'], $event->columns['version'])
                    ->pluck('cve')
                    ->unique()
                    ->join(', ');
            }

            $warning = empty($cves) ? '' : "Attention, ce paquet est vulnérable: {$cves}.";

            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le paquet {$event->columns['name']} ({$event->columns['version']}) a été installé. {$warning}",
            ];
        } elseif ($event->action === 'removed') {
            return [
                'id' => $event->id,
                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                'server' => $event->server->name,
                'ip' => $event->server->ip(),
                'message' => "Le paquet {$event->columns['name']} ({$event->columns['version']}) a été désinstallé.",
            ];
        }
        return [];
    }
}