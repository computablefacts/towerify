<?php

namespace AdversaryMeter;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Modules\AdversaryMeter\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\AdversaryMeter\Jobs\TriggerScan;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\User;
use Tests\AdversaryMeter\AdversaryMeterTestCase;

class AssetControllerTest extends AdversaryMeterTestCase
{
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'QA',
            'password' => bcrypt('whatapassword'),
            'email' => 'qa@computablefacts.com'
        ]);
        $this->token = $this->user->createToken('tests')->plainTextToken;
    }

    public function testItAllowsTheClientToAddADomain(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => 'www.example.com',
                    'type' => 'DNS',
                    'tld' => 'example.com',
                    'status' => 'invalid',
                ]
            ]);

        $asset = Asset::find($response['asset']['uid']);

        $this->assertFalse($asset->is_monitored);
    }

    public function testItAllowsTheClientToAddAnIpAddress(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => '93.184.215.14',
            'watch' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => '93.184.215.14',
                    'type' => 'IP',
                    'tld' => null,
                    'status' => 'invalid',
                ]
            ]);

        $asset = Asset::find($response['asset']['uid']);

        $this->assertFalse($asset->is_monitored);
    }

    public function testItAllowsTheClientToAddARange(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => '255.255.255.255/32',
            'watch' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => '255.255.255.255/32',
                    'type' => 'RANGE',
                    'tld' => null,
                    'status' => 'invalid',
                ]
            ]);

        $asset = Asset::find($response['asset']['uid']);

        $this->assertFalse($asset->is_monitored);
    }

    public function testItAllowsTheClientToAddADomainAndMonitorIt(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => 'www.example.com',
                    'type' => 'DNS',
                    'tld' => 'example.com',
                    'status' => 'valid',
                ]
            ]);

        $asset = Asset::find($response['asset']['uid']);

        $this->assertTrue($asset->is_monitored);
    }

    public function testItAllowsTheClientToAddAnIpAddressAndMonitorIt(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => '93.184.215.14',
            'watch' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => '93.184.215.14',
                    'type' => 'IP',
                    'tld' => null,
                    'status' => 'valid',
                ]
            ]);

        $asset = Asset::find($response['asset']['uid']);

        $this->assertTrue($asset->is_monitored);
    }

    public function testItAllowsTheClientToAddARangeAndMonitorIt(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => '255.255.255.255/32',
            'watch' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => '255.255.255.255/32',
                    'type' => 'RANGE',
                    'tld' => null,
                    'status' => 'valid',
                ]
            ]);

        $asset = Asset::find($response['asset']['uid']);

        $this->assertTrue($asset->is_monitored);
    }

    public function testInvalidAssetsAreNotCreated(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www+example+com',
            'watch' => false,
        ]);

        $response->assertStatus(500);
    }

    public function testAssetMonitoringBeginSucceedsOnNonMonitoredAsset(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => false,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/asset/{$response['asset']['uid']}/monitoring/begin");

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => 'www.example.com',
                    'type' => 'DNS',
                    'tld' => 'example.com',
                    'status' => 'valid',
                ]
            ]);
    }

    public function testAssetMonitoringEndSucceedsOnMonitoredAsset(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => true,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/asset/{$response['asset']['uid']}/monitoring/end");

        $response->assertStatus(200)
            ->assertJson([
                'asset' => [
                    'asset' => 'www.example.com',
                    'type' => 'DNS',
                    'tld' => 'example.com',
                    'status' => 'invalid',
                ]
            ]);
    }

    public function testAssetMonitoringBeginFailsOnMonitoredAsset(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => true,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/asset/{$response['asset']['uid']}/monitoring/begin");

        $response->assertStatus(500);
    }

    public function testAssetMonitoringEndFailsOnNonMonitoredAsset(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => false,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/asset/{$response['asset']['uid']}/monitoring/end");

        $response->assertStatus(500);
    }

    public function testGetTheUserAssets(): void
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
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->once()
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
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', [])
            ->andReturn([
                'scan_id' => 'a9a5d877-abed-4a39-8b4a-8316d451730d',
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

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => true,
        ]);

        TriggerScan::dispatch();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->get("/am/api/inventory/assets?valid=true");

        $response->assertStatus(200)
            ->assertJson([
                'assets' => [
                    [
                        'asset' => 'www.example.com',
                        'type' => 'DNS',
                        'tld' => 'example.com',
                        'status' => 'valid',
                        'tags' => [],
                        'tags_from_ports' => [
                            [
                                'asset' => 'www.example.com',
                                'tag' => 'cloudflare',
                                'port' => 443,
                                'is_range' => false,
                            ], [
                                'asset' => 'www.example.com',
                                'tag' => 'http',
                                'port' => 443,
                                'is_range' => false,
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function testGetAnAsset(): void
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
                    ]
                ],
            ]);
        ApiUtils::shouldReceive('ip_geoloc_public')
            ->once()
            ->with('93.184.215.14')
            ->andReturn([
                'data' => [
                    'country' => [
                        'iso_code' => 'US',
                    ],
                ],
            ]);
        ApiUtils::shouldReceive('ip_whois_public')
            ->once()
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
            ->with('www.example.com', '93.184.215.14', 443, 'tcp', [])
            ->andReturn([
                'scan_id' => 'a9a5d877-abed-4a39-8b4a-8316d451730d',
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

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/inventory/assets", [
            'asset' => 'www.example.com',
            'watch' => true,
        ]);

        TriggerScan::dispatch();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->get("/am/api/adversary/infos-from-asset/" . base64_encode('www.example.com'));

        $response->assertStatus(200)
            ->assertJson([
                'asset' => 'www.example.com',
                'modifications' => [[
                    'asset_name' => 'www.example.com',
                    'user' => 'unknown'
                ]],
                'tags' => [],
                'ports' => [
                    [
                        "ip" => "93.184.215.14",
                        "port" => 443,
                        "protocol" => "tcp",
                        "products" => ["Cloudflare http proxy"],
                        "services" => ["http"],
                        "tags" => ["http", "cloudflare"],
                        "screenshotId" => null,
                    ],
                ],
                "vulnerabilities" => [],
                "timeline" => [
                    "nmap" => [
                        "id" => "6409ae68ed42e11e31e5f19d",
                    ],
                    "sentinel" => [
                        "id" => "a9a5d877-abed-4a39-8b4a-8316d451730d",
                    ]
                ],
                "hiddenAlerts" => [],
            ]);
    }

    public function testFirstAddThenRemoveAnAssetTag(): void
    {
        $asset = Asset::firstOrCreate([
            'asset' => 'www.example.com',
            'type' => AssetTypesEnum::DNS,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->post("/am/api/facts/{$asset->id}/metadata", [
            'key' => 'DEMO', // check it will be automatically lowercased
        ]);

        $response->assertStatus(200)
            ->assertJson([
                0 => [
                    'key' => 'demo',
                ]
            ]);

        $tags = AssetTag::where('asset_id', $asset->id)->get();
        $this->assertCount(1, $tags);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ])->delete("/am/api/facts/{$asset->id}/metadata/{$response[0]['id']}");

        $response->assertStatus(200);

        $tags = AssetTag::where('asset_id', $asset->id)->get();
        $this->assertCount(0, $tags);
    }
}