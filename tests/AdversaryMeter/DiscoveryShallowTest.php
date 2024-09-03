<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Jobs\TriggerDiscoveryShallow;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryShallowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan("migrate --path=database/migrations/am --database=mysql_am");
    }

    public function testItCreatesAnAssetAfterDiscovery()
    {
        ApiUtils::shouldReceive('discover_public')
            ->once()
            ->with('example.com')
            ->andReturn([
                'subdomains' => ['www1.example.com', 'www1.example.com' /* duplicate! */, 'www2.example.com'],
            ]);

        event(new CreateAsset('example.com'));
        event(new CreateAsset('example.com', 1, 2, 3));

        TriggerDiscoveryShallow::dispatch();

        $assetsOriginal = Asset::where('asset', 'example.com')->get();
        $assetsDiscovered = Asset::whereLike('asset', 'www%.example.com')->get();

        // Ensure no duplicate in DB but a new asset for each tuple (user_id, customer_id, tenant_id)
        $this->assertEquals(2, $assetsOriginal->count());
        $this->assertEquals(4, $assetsDiscovered->count());

        // Check that a new asset is associated to each tuple (user_id, customer_id, tenant_id)
        $this->assertEquals(1, $assetsDiscovered->filter(fn(Asset $asset) => $asset->asset === 'www1.example.com' && $asset->user_id === null && $asset->customer_id === null && $asset->tenant_id === null)->count());
        $this->assertEquals(1, $assetsDiscovered->filter(fn(Asset $asset) => $asset->asset === 'www2.example.com' && $asset->user_id === null && $asset->customer_id === null && $asset->tenant_id === null)->count());

        $this->assertEquals(1, $assetsDiscovered->filter(fn(Asset $asset) => $asset->asset === 'www1.example.com' && $asset->user_id === 1 && $asset->customer_id === 2 && $asset->tenant_id === 3)->count());
        $this->assertEquals(1, $assetsDiscovered->filter(fn(Asset $asset) => $asset->asset === 'www2.example.com' && $asset->user_id === 1 && $asset->customer_id === 2 && $asset->tenant_id === 3)->count());

        // Cleanup
        $assetsOriginal->each(fn(Asset $asset) => $asset->delete());
        $assetsDiscovered->each(fn(Asset $asset) => $asset->delete());
    }
}
