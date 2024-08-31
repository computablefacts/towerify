<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TriggerDiscoveryShallow implements ShouldQueue
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
            ->where('asset_type', AssetTypesEnum::DNS)
            ->get()
            ->map(fn(Asset $asset) => $asset->tld())
            ->unique()
            ->map(fn(string $tld) => $this->discover($tld))
            ->filter(fn(array $discoveries) => isset($discoveries['subdomains']) && count($discoveries['subdomains']))
            ->flatMap(fn(array $discoveries) => collect($discoveries['subdomains'] ?? []))
            ->filter(fn(string $subdomain) => $subdomain !== '')
            ->each(function (string $subdomain) {
                Asset::updateOrCreate(
                    ['asset' => $subdomain],
                    ['asset' => $subdomain, 'asset_type' => AssetTypesEnum::DNS]
                );
            });
    }

    private function discover(string $tld): array
    {
        return ApiUtils::discover_public($tld);
    }
}
