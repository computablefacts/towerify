<?php

namespace Database\Seeds;

use App\Helpers\EventsSeeder;
use App\Models\YnhOsquery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RecentKernelModuleEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $serverId = null, int $count = 20): void
    {
        $server = EventsSeeder::findOrCreateOneServer($serverId);

        Log::debug("Create $count kernel module events for server {$server->name}(id={$server->id})");
        for ($i = 0; $i < $count; $i++) {
            YnhOsquery::factory()
                ->kernelModule()
                ->state(fn(array $attributes) => [
                    'ynh_server_id' => $server->id,
                ])
                ->create();
        }
    }
}
