<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\BeginDiscovery;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TriggerDiscoveryDeep implements ShouldQueue
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
        Asset::whereNull('discovery_id')
            ->where('type', AssetTypesEnum::DNS)
            ->get()
            ->map(fn(Asset $asset) => $asset->tld())
            ->unique()
            ->each(fn(string $tld) => event(new BeginDiscovery($tld)));
    }
}
