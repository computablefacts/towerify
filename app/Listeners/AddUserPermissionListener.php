<?php

namespace App\Listeners;

use App\Events\AddUserPermission;
use App\Events\PullServerInfos;
use Illuminate\Support\Facades\Auth;

class AddUserPermissionListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof AddUserPermission)) {
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
            $isOk = $server->sshAddUserPermission($ssh, $user2->username, $permission);

            if ($isOk) {
                PullServerInfos::dispatch($uid, $user, $server);
            }
        }
    }
}
