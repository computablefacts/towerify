<?php

namespace App\Modules\CyberBuddy\Helpers;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ClickhouseLocal
{
    public function __construct()
    {
        //
    }

    public static function executeQuery(string $query): ?string
    {
        $process = Process::fromShellCommandline("clickhouse-local --query \"{$query}\"");
        $process->setTimeout(null);
        $process->run();

        if ($process->isSuccessful()) {
            $output = trim($process->getOutput());
            return empty($output) ? 'ok' : $output;
        }

        Log::error($process->getErrorOutput());
        return null;
    }

    public static function describeTable(string $table): ?string
    {
        return self::executeQuery("DESCRIBE TABLE {$table}");
    }
}
