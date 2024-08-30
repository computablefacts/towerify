<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan("migrate --path=database/migrations/am --database=mysql_am");
    }

    public function testItUpdatesTld()
    {
        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);

        $asset = Asset::find($asset->id); // reload all fields from db

        $this->assertNull($asset->tld);
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);
        $this->assertNull($asset->discovery_id);

        $tld = $asset->tld();

        $this->assertEquals($tld, 'example.com');
        $this->assertEquals($asset->tld, 'example.com');

        $asset = Asset::find($asset->id); // reload from db

        $this->assertEquals($asset->tld, 'example.com'); // ensure TLD has been persisted

        $asset->delete(); // cleanup
    }

    public function testItDoesNotUpdateTld()
    {
        $asset = Asset::firstOrCreate([
            'asset' => '93.184.215.14',
            'asset_type' => AssetTypesEnum::IP,
        ]);

        $asset = Asset::find($asset->id); // reload all fields from db

        $this->assertNull($asset->tld);
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);
        $this->assertNull($asset->discovery_id);

        $tld = $asset->tld();

        $this->assertNull($tld);
        $this->assertNull($asset->tld);

        $asset = Asset::find($asset->id); // reload from db

        $this->assertNull($asset->tld); // ensure TLD has been persisted

        $asset->delete(); // cleanup
    }
}
