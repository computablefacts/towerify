<?php

namespace Tests\AdversaryMeter;

use App\Modules\AdversaryMeter\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdversaryMeterTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("migrate --path=database/migrations --database=mysql");
        $this->artisan("migrate --path=database/migrations/am --database=mysql_am");
    }

    protected function tearDown(): void
    {
        Asset::whereNotNull('id')->delete();
        parent::tearDown();
    }
}
