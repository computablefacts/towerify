<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAssetTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan("migrate --path=database/migrations/am --database=mysql_am");
    }

    public function testItCreatesAnIpAddress()
    {
        event(new CreateAsset('93.184.215.14', 1, 2, 3));

        $asset = Asset::where('asset', '93.184.215.14')->firstOrFail();

        $this->assertNull($asset->tld);
        $this->assertNull($asset->tld());
        $this->assertEquals(AssetTypesEnum::IP, $asset->asset_type);
        $this->assertEquals(1, $asset->user_id);
        $this->assertEquals(2, $asset->customer_id);
        $this->assertEquals(3, $asset->tenant_id);

        $asset->delete(); // cleanup
    }

    public function testItCreatesADomain()
    {
        event(new CreateAsset('www.example.com', 1, 2, 3));

        $asset = Asset::where('asset', 'www.example.com')->firstOrFail();

        $this->assertNull($asset->tld);
        $this->assertEquals('example.com', $asset->tld());
        $this->assertEquals(AssetTypesEnum::DNS, $asset->asset_type);
        $this->assertEquals(1, $asset->user_id);
        $this->assertEquals(2, $asset->customer_id);
        $this->assertEquals(3, $asset->tenant_id);

        $asset->delete(); // cleanup
    }

    public function testItCreatesARange()
    {
        event(new CreateAsset('255.255.255.255/32', 1, 2, 3));

        $asset = Asset::where('asset', '255.255.255.255/32')->firstOrFail();

        $this->assertNull($asset->tld);
        $this->assertNull($asset->tld());
        $this->assertEquals(AssetTypesEnum::RANGE, $asset->asset_type);
        $this->assertEquals(1, $asset->user_id);
        $this->assertEquals(2, $asset->customer_id);
        $this->assertEquals(3, $asset->tenant_id);

        $asset->delete(); // cleanup
    }

    public function testItDoesNotCreateDuplicates()
    {
        event(new CreateAsset('www.example.com'));
        event(new CreateAsset('www.example.com', 1, 2, 3));
        event(new CreateAsset('www.example.com', 1, 2, 3));

        $assets = Asset::where('asset', 'www.example.com')->get();

        $this->assertEquals(2, $assets->count());
        $this->assertEquals(1, $assets->filter(fn(Asset $asset) => $asset->user_id === null)->count());
        $this->assertEquals(1, $assets->filter(fn(Asset $asset) => $asset->user_id === 1)->count());
        
        $assets->each(fn(Asset $asset) => $asset->delete()); // cleanup
    }
}
