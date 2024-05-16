<?php

namespace App\Jobs;

use App\Models\YnhServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullServersInfos implements ShouldQueue
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
        YnhServer::whereNotNull('ip_address')
            ->whereNotNull('ssh_port')
            ->whereNotNull('ssh_username')
            ->whereNotNull('ssh_public_key')
            ->whereNotNull('ssh_private_key')
            ->each(function (YnhServer $server) {
                $server->pullServerInfos();
            });
    }
}
