<?php

namespace App\Jobs;

use App\Enums\ServerStatusEnum;
use App\Models\YnhServer;
use App\Notifications\HealthCheckIssue;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckServersHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function handle()
    {
        $minDate = Carbon::now()->subMinutes(20);
        YnhServer::where('is_ready', true)
            ->where('is_frozen', false)
            ->whereNotExists(function ($query) use ($minDate) {
                $query->select(DB::raw('1'))
                    ->from('ynh_osquery')
                    ->whereRaw('ynh_osquery.ynh_server_id = ynh_servers.id')
                    ->where('ynh_osquery.calendar_time', '>=', $minDate->toDateTimeString());
            })
            ->get()
            ->filter(fn(YnhServer $server) => $server->status() !== ServerStatusEnum::RUNNING)
            ->each(function (YnhServer $server) {
                $users = User::where('tenant_id', $server->tenant()?->id)
                    ->get()
                    ->filter(fn(User $user) => $user->canManageServers())
                    ->all();
                Notification::send($users, new HealthCheckIssue($server));
            });
    }
}
