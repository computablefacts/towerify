<?php

namespace App\Listeners;

use App\Events\ProcessLogparserPayload;
use App\Models\YnhNginxLogs;
use App\Models\YnhServer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLogparserPayloadListener extends AbstractListener
{
    public function viaQueue(): string
    {
        return self::LOW;
    }

    protected function handle2($event)
    {
        if (!($event instanceof ProcessLogparserPayload)) {
            throw new \Exception('Invalid event type!');
        }

        $server = $event->server;
        $logs = $event->logs;
        $toId = $server->id;
        $toIp = $server->ip();
        $fromId = [];

        foreach ($logs as $countServiceAndIp) {

            $count = $countServiceAndIp['count'];
            $service = $countServiceAndIp['service'];
            $fromIp = $countServiceAndIp['ip'];

            if (!array_key_exists($fromIp, $fromId)) {
                $fromServer = YnhServer::where('ip_address', $fromIp)->first();
                if ($fromServer) {
                    $fromId[$fromIp] = $fromServer->id;
                } else {
                    $fromServer = YnhServer::where('ip_address_v6', $fromIp)->first();
                    if ($fromServer) {
                        $fromId[$fromIp] = $fromServer->id;
                    }
                }
            }

            YnhNginxLogs::updateOrCreate([
                'from_ip_address' => $fromIp,
                'to_ynh_server_id' => $toId,
                'service' => $service,
            ], [
                'from_ynh_server_id' => $fromId[$fromIp] ?? null,
                'to_ynh_server_id' => $toId,
                'from_ip_address' => $fromIp,
                'to_ip_address' => $toIp,
                'service' => $service,
                'weight' => $count,
                'updated' => true,
            ]);
        }
        DB::transaction(function () use ($server) {
            YnhNginxLogs::where('to_ynh_server_id', $server->id)
                ->where('updated', false)
                ->delete();
            YnhNginxLogs::where('to_ynh_server_id', $server->id)
                ->update(['updated' => false]);
        });
        Log::debug("LogParser - server_name={$server->name}, server_ip={$server->ip()}, nb_logs_ingested={$logs->count()}");
    }
}
