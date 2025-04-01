<?php

namespace Database\Seeds;

use App\Helpers\EventsSeeder;
use Illuminate\Database\Seeder;

class RecentEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servers = EventsSeeder::findOrCreateServers(3);
        $servers->map(function ($server) {
            $this->call(RecentLoginOrLogoutEventsSeeder::class, true, ['serverId' => $server->id, 'count' => 10]);
            $this->call(RecentAuthorizedKeyEventsSeeder::class, true, ['serverId' => $server->id, 'count' => 10]);
            $this->call(RecentUserSshKeyEventsSeeder::class, true, ['serverId' => $server->id, 'count' => 10]);
            $this->call(RecentUserAccountEventsSeeder::class, true, ['serverId' => $server->id, 'count' => 10]);
            $this->call(RecentGroupEventsSeeder::class, true, ['serverId' => $server->id, 'count' => 10]);
            $this->call(RecentPackageEventsSeeder::class, true, ['serverId' => $server->id, 'count' => 10]);
        });
    }
}
