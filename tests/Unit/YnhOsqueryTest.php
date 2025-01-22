<?php

namespace Tests\Unit;

use App\Models\YnhOsquery;
use Tests\TestCaseNoDb;

class YnhOsqueryTest extends TestCaseNoDb
{
    public function testComputeColumnsUidOnArray()
    {
        $uid = YnhOsquery::computeColumnsUid(["a", "b", "c"]);
        $this->assertEquals("b05555a5d6d4b64fad478a407d21ffd1", $uid);

        $uid = YnhOsquery::computeColumnsUid(["b", "a", "c"]);
        $this->assertEquals("b919231a1aec8102cacdbf55bf397471", $uid);
    }

    public function testComputeColumnsUidOnAssociativeArray()
    {
        $uid = YnhOsquery::computeColumnsUid(["id" => 1, "array" => ["id" => 1, "array" => ["a", "b", "c"]]]);
        $this->assertEquals("bf1823f8153cfc357a0bfa61e1e2c6a4", $uid);

        $uid = YnhOsquery::computeColumnsUid(["array" => ["id" => 1, "array" => ["a", "b", "c"]], "id" => 1]);
        $this->assertEquals("bf1823f8153cfc357a0bfa61e1e2c6a4", $uid);

        $uid = YnhOsquery::computeColumnsUid(["id" => 1, "array" => ["id" => 1, "array" => ["b", "a", "c"]]]);
        $this->assertEquals("fb7224001217899d790af7b30f4d8b55", $uid);

        $uid = YnhOsquery::computeColumnsUid(["id" => 1, "array" => ["array" => ["b", "a", "c"], "id" => 1]]);
        $this->assertEquals("fb7224001217899d790af7b30f4d8b55", $uid);
    }
}
