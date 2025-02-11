<?php

namespace App\Modules\CyberBuddy\Helpers;

use Illuminate\Support\Str;

class ClickhouseUtils
{
    private function __construct()
    {
        //
    }

    public static function normalizeTableName(string $name): string
    {
        return Str::replace(['-', ' '], '_', Str::lower(Str::beforeLast(Str::afterLast($name, '/'), '.')));
    }

    public static function normalizeColumnName(string $name): string
    {
        return Str::upper(Str::replace([' '], '_', $name));
    }
}
