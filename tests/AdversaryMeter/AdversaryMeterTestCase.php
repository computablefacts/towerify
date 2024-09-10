<?php

namespace Tests\AdversaryMeter;

use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdversaryMeterTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan("migrate --path=database/migrations --database=mysql");
        $this->artisan("migrate --path=database/migrations/am --database=mysql_am");

        $this->user = User::create([
            'name' => 'QA',
            'password' => bcrypt('whatapassword'),
            'email' => 'qa@computablefacts.com'
        ]);
        $this->token = $this->user->createToken('tests', [])->plainTextToken;
        $this->user->am_api_token = $this->token;
        $this->user->save();
    }

    protected function tearDown(): void
    {
        Asset::whereNotNull('id')->delete();
        Honeypot::whereNotNull('id')->delete();
        Attacker::whereNotNull('id')->delete();
        parent::tearDown();
    }
}
