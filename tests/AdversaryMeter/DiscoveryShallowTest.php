<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Jobs\TriggerDiscoveryShallow;
use App\Modules\AdversaryMeter\Models\Asset;
use Tests\AdversaryMeter\AdversaryMeterTestCase;

class DiscoveryShallowTest extends AdversaryMeterTestCase
{
    public function testItCreatesAnAssetAfterDiscovery()
    {
        ApiUtils::shouldReceive('discover_public')
            ->once()
            ->with('example.com')
            ->andReturn([
                'subdomains' => ['www1.example.com', 'www1.example.com' /* duplicate! */, 'www2.example.com'],
            ]);

        event(new CreateAsset($this->user, 'example.com'));
        event(new CreateAsset($this->user, 'example.com'));

        TriggerDiscoveryShallow::dispatch();

        $assetsOriginal = Asset::where('asset', 'example.com')->get();
        $assetsDiscovered = Asset::whereLike('asset', 'www%.example.com')->get();

        // Ensure no duplicate in DB
        $this->assertEquals(1, $assetsOriginal->count());
        $this->assertEquals(2, $assetsDiscovered->count());

        $this->assertEquals(1, $assetsDiscovered->filter(fn(Asset $asset) => $asset->asset === 'www1.example.com' && $asset->created_by = $this->user->id)->count());
        $this->assertEquals(1, $assetsDiscovered->filter(fn(Asset $asset) => $asset->asset === 'www2.example.com' && $asset->created_by = $this->user->id)->count());
    }
}
