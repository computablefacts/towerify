<?php

namespace Tests;

use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\HiddenAlert;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        if ('testing' !== app()->environment()) {
            echo("The environment is not testing. I quit. This would likely destroy data.\n");
            exit(1);
        }

        $this->seed();

        $this->user = User::where('email', 'qa@computablefacts.com')->firstOrfail();
        $this->token = $this->user->createToken('tests', [])->plainTextToken;
        $this->user->am_api_token = $this->token;
        $this->user->save();
    }

    protected function tearDown(): void
    {
        Asset::whereNotNull('id')->delete();
        Honeypot::whereNotNull('id')->delete();
        Attacker::whereNotNull('id')->delete();
        HiddenAlert::whereNotNull('id')->delete();
        parent::tearDown();
    }
}
