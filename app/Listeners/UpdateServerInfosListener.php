<?php

namespace App\Listeners;

use App\Events\PullServerInfos;
use Illuminate\Support\Facades\Auth;

class UpdateServerInfosListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof PullServerInfos)) {
            throw new \Exception('Invalid event type!');
        }

        $uid = $event->uid;
        $user = $event->user;
        $server = $event->server;

        Auth::login($user); // otherwise the tenant will not be properly set

        if ($server && $server->isReady()) {
            $server->pullServerInfos($uid, $user);
        }
    }
}
