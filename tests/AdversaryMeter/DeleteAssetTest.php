<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Events\CreateAsset;
use App\Modules\AdversaryMeter\Events\DeleteAsset;
use App\Modules\AdversaryMeter\Models\Asset;
use Tests\AdversaryMeter\AdversaryMeterTestCase;

class DeleteAssetTest extends AdversaryMeterTestCase
{
    public function testItDeletesAllAssets()
    {
        event(new CreateAsset('93.184.215.14'));
        event(new CreateAsset('www.example.com', 1));
        event(new CreateAsset('255.255.255.255/32', 1, 2));

        $asset1 = Asset::where('asset', '93.184.215.14')->firstOrFail();
        $asset2 = Asset::where('asset', 'www.example.com')->firstOrFail();
        $asset3 = Asset::where('asset', '255.255.255.255/32')->firstOrFail();

        event(new DeleteAsset('93.184.215.14'));
        event(new DeleteAsset('www.example.com', 1));
        event(new DeleteAsset('255.255.255.255/32', 1, 2));

        $asset1 = Asset::where('asset', '93.184.215.14')->first();
        $asset2 = Asset::where('asset', 'www.example.com')->first();
        $asset3 = Asset::where('asset', '255.255.255.255/32')->first();

        $this->assertNull($asset1);
        $this->assertNull($asset2);
        $this->assertNull($asset3);
    }
}
