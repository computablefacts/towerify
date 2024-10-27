<?php

namespace App\Listeners;

use App\Models\YnhServer;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof PasswordReset)) {
            throw new \Exception('Invalid event type!');
        }

        /** @var User $user */
        $user = $event->user;

        YnhServer::select('ynh_servers.*')
            ->join('ynh_users', 'ynh_users.ynh_server_id', '=', 'ynh_servers.id')
            ->where('ynh_users.email', $user->email)
            ->get()
            ->each(function (YnhServer $server) use ($user) {
                $ssh = $server->sshConnection(Str::random(10), null);
                $server->sshCreateOrUpdateUserProfile($ssh, $user->name, $user->email, $user->ynhUsername(), $user->ynhPassword());
            });
    }
}
