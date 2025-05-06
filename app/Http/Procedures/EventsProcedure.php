<?php

namespace App\Http\Procedures;

use App\Models\YnhOsquery;
use App\Models\YnhServer;
use Illuminate\Http\Request;
use Sajya\Server\Attributes\RpcMethod;
use Sajya\Server\Procedure;

class EventsProcedure extends Procedure
{
    public static string $name = 'events';

    #[RpcMethod(
        description: "Dismiss an event (false positive).",
        params: [
            'event_id' => 'The event identifier.',
        ],
        result: [
            "msg" => "A success message.",
        ]
    )]
    public function dismiss(Request $request): array
    {
        if (!$request->user()->canUseAgents()) {
            throw new \Exception('Missing permission.');
        }

        $params = $request->validate([
            'event_id' => 'required|integer|exists:ynh_osquery,id',
        ]);

        /** @var YnhOsquery $event */
        $event = YnhOsquery::find($params['event_id']);
        /** @var YnhServer $server */
        $server = YnhServer::find($event->ynh_server_id);

        if (!$server) {
            throw new \Exception("Unknown server.");
        }

        $event->dismissed = true;
        $event->save();

        return [
            "msg" => "The event has been dismissed!",
        ];
    }
}