<?php

namespace App\Modules\AdversaryMeter\Jobs;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
            ->where('type', AssetTypesEnum::DNS)
            ->get()
            ->map(fn(Asset $asset) => $asset->tld())
            ->unique()
            ->each(function (string $tld) {

                $discovered = $this->discover($tld);

                if (isset($discovered['subdomains']) && count($discovered['subdomains'])) {
                    collect($discovered['subdomains'])
                        ->filter(fn(string $domain) => !empty($domain))
                        ->each(function (string $domain) use ($tld) {
                            Asset::where('tld', $tld)
                                ->get()
                                ->filter(function (Asset $asset) {
                                    // Deal with clients using one of our many domains...
                                    return $asset->createdBy()->email === config('towerify.admin.email')
                                        || !Str::endsWith($asset->asset, ['computablefacts.com', 'computablefacts.io', 'towerify.io', 'cywise.io']);
                                })
                                ->each(function (Asset $asset) use ($domain) {
                                    event(new CreateAsset($asset->createdBy(), $domain, true));
                                });
                        });
                }
            });
    }

    private function discover(string $tld): array
    {
        return ApiUtils::discover_public($tld);
    }
}
