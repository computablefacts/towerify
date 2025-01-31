<?php

namespace App\Modules\CyberBuddy\Helpers;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ClickhouseClient
{

    public function __construct()
    {
        //
    }

    public static function executeQuery(string $query): ?string
    {
        $process = Process::fromShellCommandline(self::cmd($query));
        $process->setTimeout(null);
        $process->run();

        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }

        Log::error($process->getErrorOutput());
        return null;
    }

    public static function showTables(): ?string
    {
        return self::executeQuery("SHOW TABLES");
    }

    public static function describeTable(string $table): ?string
    {
        return self::executeQuery("DESCRIBE TABLE {$table}");
    }

    public static function dropTableIfExists(string $table): ?string
    {
        return self::executeQuery("DROP TABLE IF EXISTS {$table}");
    }

    public static function renameTable(string $oldName, string $newName): ?string
    {
        return self::executeQuery("RENAME TABLE {$oldName} TO {$newName}");
    }

    private static function cmd(string $query): string
    {
        $host = config('towerify.clickhouse.host');
        $username = config('towerify.clickhouse.username');
        $password = config('towerify.clickhouse.password');
        $database = config('towerify.clickhouse.database');
        return "clickhouse-client --host '{$host}' --secure --user '{$username}' --password '{$password}' --database '{$database}' --query \"{$query}\"";
    }
}
