<?php

namespace Database\Seeds;

use App\Helpers\EventsSeeder;
use App\Models\YnhOsquery;
use App\Models\YnhServer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RecentGroupEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $serverId = null, int $count = 20): void
    {
        $server = YnhServer::query()->where('id', '=', $serverId)->first();

        if ($server === null) {
            Log::debug('No server provided: create one or use an existing one');
            $server = EventsSeeder::findOrCreateServers()->shuffle()->first();
        }

        Log::debug("Create $count group events for server {$server->name}(id={$server->id})");
        for ($i = 0; $i < $count; $i++) {
            YnhOsquery::factory()
                ->group()
                ->state(fn(array $attributes) => [
                    'ynh_server_id' => $server->id,
                ])
                ->create();
        }
    }
}
