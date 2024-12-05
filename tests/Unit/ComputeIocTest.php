<?php

namespace Tests\Unit;

use App\Jobs\ComputeIoc;
use Tests\TestCase;

class ComputeIocTest extends TestCase
{
    public function testComputeVariance()
    {
        $variance = ComputeIoc::variance(collect([0, 0, 0, 0, 0]));
        $this->assertEquals(0, $variance);

        $variance = ComputeIoc::variance(collect([1, 2, 3, 4, 5]));
        $this->assertEquals(2, $variance);

        $variance = ComputeIoc::variance(collect([3, 5, 8, 1]));
        $this->assertEqualsWithDelta(6.6875, $variance, 0.0001);

        $variance = ComputeIoc::variance(collect([6, 9, 14, 10, 5, 8, 1]));
        $this->assertEqualsWithDelta(14.531, $variance, 0.001);

        $variance = ComputeIoc::variance(collect([3, 4, 6, 7, 7, 9, 13]));
        $this->assertEqualsWithDelta(9.4286, $variance, 0.0001);
    }

    public function testComputeStdDev()
    {
        $stdDev = ComputeIoc::stdDev(collect([0, 0, 0, 0, 0]));
        $this->assertEquals(0, $stdDev);

        $stdDev = ComputeIoc::stdDev(collect([1, 2, 3, 4, 5]));
        $this->assertEqualsWithDelta(1.4142, $stdDev, 0.0001);

        $stdDev = ComputeIoc::stdDev(collect([3, 5, 8, 1]));
        $this->assertEqualsWithDelta(2.5860, $stdDev, 0.0001);

        $variance = ComputeIoc::stdDev(collect([6, 9, 14, 10, 5, 8, 1]));
        $this->assertEqualsWithDelta(3.8119, $variance, 0.0001);

        $variance = ComputeIoc::stdDev(collect([3, 4, 6, 7, 7, 9, 13]));
        $this->assertEqualsWithDelta(3.0705, $variance, 0.0001);
    }

    public function testComputeAnomaly()
    {
        $numbers = collect([200, 210, 215, 190, 205, 400, 198, 202, 220, 210, 205, 195, 200, 180, 250,
            205, 300, 215, 205, 190, 200, 210, 205, 600, 190, 200, 180, 195, 205, 210]);

        // Lower bound is 65.4
        $this->assertTrue(ComputeIoc::isAnomaly(65.3, $numbers));
        $this->assertFalse(ComputeIoc::isAnomaly(65.4, $numbers));

        // Upper bound is 387.27
        $this->assertFalse(ComputeIoc::isAnomaly(387.2, $numbers));
        $this->assertTrue(ComputeIoc::isAnomaly(387.3, $numbers));
    }
}
