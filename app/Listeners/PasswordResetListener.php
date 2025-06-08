<?php

namespace App\Listeners;

use App\Models\YnhServer;
use App\Models\User;
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

        User::init($user);

        YnhServer::select('ynh_servers.*')
            ->join('ynh_users', 'ynh_users.ynh_server_id', '=', 'ynh_servers.id')
            ->where('ynh_users.email', $user->email)
            ->whereNotNull('ynh_servers.ip_address')
            ->whereNotNull('ynh_servers.ssh_port')
            ->whereNotNull('ynh_servers.ssh_username')
            ->whereNotNull('ynh_servers.ssh_public_key')
            ->whereNotNull('ynh_servers.ssh_private_key')
            ->where('ynh_servers.is_ready', true)
            ->where('ynh_servers.added_with_curl', false)
            ->where('ynh_servers.is_frozen', false)
            ->get()
            ->filter(fn(YnhServer $server) => $server->isYunoHost())
            ->filter(fn(YnhServer $server) => $server->sshTestConnection())
            ->each(function (YnhServer $server) use ($user) {
                $ssh = $server->sshConnection(Str::random(10), null);
                $server->sshCreateOrUpdateUserProfile($ssh, $user->name, $user->email, $user->ynhUsername(), $user->ynhPassword());
            });
    }
}
