<?php

namespace App\Notifications;

use App\Enums\NotificationLevelEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\ServerStatusEnum;
use App\Models\YnhServer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HealthCheckIssue extends Notification
{
    use Queueable;

    private YnhServer $server;

    /**
     * Create a new notification instance.
     */
    public function __construct(YnhServer $server)
    {
        $this->server = $server;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($this->server->isFrozen()) { // no metric expected from this server
            return [];
        }
        if (!$this->server->isReady()) { // no metric expected from this server
            return [];
        }
        if ($this->server->status() === ServerStatusEnum::RUNNING) { // the server came back online
            return [];
        }
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationTypeEnum::HEALTHCHECK_ISSUE->value,
            'level' => NotificationLevelEnum::DANGER->value,
            'message' => "A health check issue has been detected: no metrics have been recorded in the past 20 minutes.",
            'details' => [
                'server_name' => $this->server->name,
                'principal_domain' => $this->server->domain()?->name,
                'ip_v4' => $this->server->ip(),
                'ip_v6' => $this->server->ipv6(),
                'last_heartbeat' => $this->server->lastHeartbeat() ? $this->server->lastHeartbeat()->format('Y-m-d H:i:s') . ' UTC' : null,
            ],
            'action' => [
                'name' => 'resources usage',
                'url' => url("/ynh/servers/{$this->server->id}/edit?tab=resources_usage"),
            ],
        ];
    }
}
