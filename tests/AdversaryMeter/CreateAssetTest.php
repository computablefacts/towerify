<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use Tests\AdversaryMeter\AdversaryMeterTestCase;

class CreateAssetTest extends AdversaryMeterTestCase
{
    public function testItCreatesAnIpAddress()
    {
        event(new CreateAsset('93.184.215.14'));

        $asset = Asset::where('asset', '93.184.215.14')->firstOrFail();

        $this->assertNull($asset->tld);
        $this->assertNull($asset->tld());
        $this->assertEquals(AssetTypesEnum::IP, $asset->type);
    }

    public function testItCreatesADomain()
    {
        event(new CreateAsset('www.example.com'));

        $asset = Asset::where('asset', 'www.example.com')->firstOrFail();

        $this->assertNull($asset->tld);
        $this->assertEquals('example.com', $asset->tld());
        $this->assertEquals(AssetTypesEnum::DNS, $asset->type);
    }

    public function testItCreatesARange()
    {
        event(new CreateAsset('255.255.255.255/32'));

        $asset = Asset::where('asset', '255.255.255.255/32')->firstOrFail();

        $this->assertNull($asset->tld);
        $this->assertNull($asset->tld());
        $this->assertEquals(AssetTypesEnum::RANGE, $asset->type);
    }

    public function testItDoesNotCreateDuplicates()
    {
        event(new CreateAsset('www.example.com'));
        event(new CreateAsset('www.example.com'));
        event(new CreateAsset('www.example.com'));

        $assets = Asset::where('asset', 'www.example.com')->get();

        $this->assertEquals(1, $assets->count());
        $this->assertEquals(1, $assets->filter(fn(Asset $asset) => $asset->user_id === null)->count());
    }
}
