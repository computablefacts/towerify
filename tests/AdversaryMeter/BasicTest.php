<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Jobs\TriggerScan;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\PortTag;
use App\Modules\AdversaryMeter\Models\Scan;
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

    public function testItTriggersAnAssetScan()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => '6409ae68ed42e11e31e5f19d',
            ]);
        ApiUtils::shouldReceive('task_status_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_status' => 'SUCCESS',
            ]);
        ApiUtils::shouldReceive('task_result_public')
            ->once()
            ->with('6409ae68ed42e11e31e5f19d')
            ->andReturn([
                'task_result' => [
                    [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 443,
                        'protocol' => 'tcp',
                    ], [
                        'hostname' => 'www.example.com',
                        'ip' => '93.184.215.14',
                        'port' => 80,
                        'protocol' => 'tcp',
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->twice()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->twice()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'asn_description' => null,
                    'asn_registry' => null,
                    'asn' => null,
                    'asn_cidr' => null,
                    'asn_country_code' => null,
                    'asn_date' => null,
                ],
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => 'a9a5d877-abed-4a39-8b4a-8316d451730d',
            ]);
        ApiUtils::shouldReceive('task_start_scan_public')
            ->once()
            ->with('www.example.com', '93.184.215.14', 80, 'tcp', ['demo'])
            ->andReturn([
                'scan_id' => 'b9b5e877-bdfe-4b39-8c4b-8316e451730e',
            ]);
        ApiUtils::shouldReceive('task_get_scan_public')
            ->once()
            ->with('a9a5d877-abed-4a39-8b4a-8316d451730d')
            ->andReturn([
                'hostname' => 'www.example.com',
                'ip' => '93.184.215.14',
                'port' => 443,
                'protocol' => 'tcp',
                'service' => 'http',
                'product' => 'Cloudflare http proxy',
                'ssl' => true,
                'current_task' => 'alerter',
                'current_task_status' => 'DONE',
                'tags' => ['Http', 'Cloudflare'],
                'data' => [
                    //
                ],
            ]);
        ApiUtils::shouldReceive('task_get_scan_public')
            ->once()
            ->with('b9b5e877-bdfe-4b39-8c4b-8316e451730e')
            ->andReturn([
                'hostname' => 'www.example.com',
                'ip' => '93.184.215.14',
                'port' => 80,
                'protocol' => 'tcp',
                'service' => 'http',
                'product' => 'Cloudflare http proxy',
                'ssl' => false,
                'current_task' => 'alerter',
                'current_task_status' => 'DONE',
                'tags' => ['Http', 'Cloudflare'],
                'data' => [
                    //
                ],
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertEquals('6409ae68ed42e11e31e5f19d', $asset->cur_scan_id); // Events are sync during tests...
        $this->assertNull($asset->next_scan_id);

        // Check the assets_tags table
        $assetTags = AssetTag::where('asset_id', $asset->id)->get();
        $this->assertEquals(1, $assetTags->count());

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(2, $scans->count());

        // Check the ports table
        $ports = Port::whereIn('scan_id', $scans->pluck('id'))->get();
        $this->assertEquals(2, $ports->count());

        // Check the ports_tags table
        $portsTags = PortTag::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(4, $portsTags->count());

        // Check the alerts table
        $alerts = Alert::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $alerts->count());

        // Cleanup
        $asset->delete();

        // Ensure that removing an asset remove all associated data
        $assetId = $asset->id;
        $asset = Asset::find($assetId);
        $this->assertNull($asset);

        // Check the assets_tags table
        $assetTags = AssetTag::where('asset_id', $assetId)->get();
        $this->assertEquals(0, $assetTags->count());

        // Check the scans table
        $scans = Scan::where('asset_id', $assetId)->get();
        $this->assertEquals(0, $scans->count());

        // Check the ports table
        $ports = Port::whereIn('scan_id', $scans->pluck('id'))->get();
        $this->assertEquals(0, $ports->count());

        // Check the ports_tags table
        $portsTags = PortTag::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $portsTags->count());

        // Check the alerts table
        $alerts = Alert::whereIn('port_id', $ports->pluck('id'))->get();
        $this->assertEquals(0, $alerts->count());
    }

    public function testItDoesNotModifyTheDbWhenPortsScanFailsToStart()
    {
        ApiUtils::shouldReceive('task_nmap_public')
            ->once()
            ->with('www.example.com')
            ->andReturn([
                'task_id' => null,
            ]);

        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'asset_type' => AssetTypesEnum::DNS,
        ]);
        $asset->tags()->create(['tag' => 'demo']);

        TriggerScan::dispatch();

        $asset = Asset::find($asset->id); // reload from db

        // Check the assets table
        $this->assertNull($asset->prev_scan_id);
        $this->assertNull($asset->cur_scan_id);
        $this->assertNull($asset->next_scan_id);

        // Check the scans table
        $scans = Scan::where('asset_id', $asset->id)->get();
        $this->assertEquals(0, $scans->count());

        // Cleanup
        $asset->delete();
    }
}
