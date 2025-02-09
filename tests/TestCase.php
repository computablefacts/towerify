<?php

namespace Tests;

use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Attacker;
use App\Modules\AdversaryMeter\Models\HiddenAlert;
use App\Modules\AdversaryMeter\Models\Honeypot;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\Scan;
use App\Modules\AdversaryMeter\Models\Screenshot;
use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * To create the test database:
 * <pre>
 *     CREATE DATABASE tw_testdb DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 * </pre>
 *
 * To create the test user:
 * <pre>
 *     CREATE USER 'tw_testuser'@'localhost' IDENTIFIED BY 'z0rglub';
 *     GRANT ALL ON tw_testdb.* TO 'tw_testuser'@'localhost';
 *     FLUSH PRIVILEGES;
 * </pre>
 *
 * See https://dwij.net/how-to-speed-up-laravel-unit-tests-using-schemadump/
 */
abstract class TestCase extends BaseTestCase
{
    protected User $user;
    protected string $token;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        print "\nSETTING UP DATABASE\n";
        shell_exec('php artisan migrate:fresh --env=testing --seed');
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ('testing' !== app()->environment()) {
            echo("The environment is not testing. I quit. This would likely destroy data.\n");
            exit(1);
        }

        $this->user = User::where('email', 'qa@computablefacts.com')->firstOrfail();
        $this->token = $this->user->createToken('tests', [])->plainTextToken;
        $this->user->am_api_token = $this->token;
        $this->user->save();
    }

    protected function tearDown(): void
    {
        Alert::whereNotNull('id')->delete();
        Asset::whereNotNull('id')->delete();
        Attacker::whereNotNull('id')->delete();
        HiddenAlert::whereNotNull('id')->delete();
        Honeypot::whereNotNull('id')->delete();
        Port::whereNotNull('id')->delete();
        Scan::whereNotNull('id')->delete();
        Screenshot::whereNotNull('id')->delete();
        parent::tearDown();
    }
}
