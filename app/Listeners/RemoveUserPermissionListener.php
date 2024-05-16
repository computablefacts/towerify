<?php

namespace App\Listeners;

use App\Events\PullServerInfos;
use App\Events\RemoveUserPermission;
use Illuminate\Support\Facades\Auth;

class RemoveUserPermissionListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof RemoveUserPermission)) {
            throw new \Exception('Invalid event type!');
        }

        $uid = $event->uid;
        $user = $event->user;
        $server = $event->server;
        $user2 = $event->ynhUser;
        $permission = $event->permission;

        Auth::login($user); // otherwise the tenant will not be properly set

        if ($server && $server->isReady()) {

            $ssh = $server->sshConnection($uid, $user);
            $isOk = $server->sshRemoveUserPermission($ssh, $user2->username, $permission);

            if ($isOk) {
                event(new PullServerInfos($uid, $user, $server));
            }
        }
    }
}
