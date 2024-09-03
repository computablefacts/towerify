<?php

namespace AdversaryMeter;

use App\User;
use Tests\AdversaryMeter\AdversaryMeterTestCase;

class InventoryControllerTest extends AdversaryMeterTestCase
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
    }
}