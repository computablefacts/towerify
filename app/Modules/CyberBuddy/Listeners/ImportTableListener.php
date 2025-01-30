<?php

namespace App\Modules\CyberBuddy\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\CyberBuddy\Events\ImportTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ImportTableListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof ImportTable)) {
            throw new \Exception('Invalid event type!');
        }

        $region = $event->region;
        $accessKeyId = $event->accessKeyId;
        $secretAccessKey = $event->secretAccessKey;
        $inputFolder = $event->inputFolder;
        $outputFolder = $event->outputFolder;
        $copy = $event->copy;
        $deduplicate = $event->deduplicate;
        $table = $event->table;
        $columns = $event->columns;

        Auth::login($event->user); // otherwise the tenant will not be properly set

        try {

            // Misc. parameters
            $clickhouseHost = config('towerify.clickhouse.host');
            $clickhouseUsername = config('towerify.clickhouse.username');
            $clickhousePassword = config('towerify.clickhouse.password');
            $clickhouseDatabase = config('towerify.clickhouse.database');
            $filename = Str::replace(['-', ' '], '_', Str::lower(Str::beforeLast(Str::afterLast($table, '/'), '.')));
            $bucket = explode('/', $inputFolder, 2)[0];
            $s3In = "s3('https://s3.{$region}.amazonaws.com/{$bucket}/{$table}', '{$accessKeyId}', '{$secretAccessKey}', 'TabSeparatedWithNames')";
            $s3Out = "s3('https://s3.{$region}.amazonaws.com/{$outputFolder}{$filename}.parquet', '{$accessKeyId}', '{$secretAccessKey}', 'Parquet')";
            $colNames = collect($columns)->map(fn(array $column) => "{$column['old_name']} AS {$column['new_name']}")->join(",");
            $distinct = $deduplicate ? "DISTINCT" : "";

            Log::debug("Importing table {$table} from S3 to clickhouse server");
            Log::debug("Input file: {$s3In}");
            Log::debug("Output file: {$s3Out}");

            // Transform the TSV file to a Parquet file and write it to the user-defined output directory
            $query = "INSERT INTO FUNCTION {$s3Out} SELECT {$distinct} {$colNames} FROM {$s3In} SETTINGS s3_create_new_file_on_insert=1";
            $process = Process::fromShellCommandline("clickhouse-local --query \"{$query}\"");
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("An error occurred while converting the table {$table}: {$process->getErrorOutput()}");
                return;
            }

            // Get the table schema from the parquet file
            $query = "DESCRIBE TABLE {$s3Out}";
            $process = Process::fromShellCommandline("clickhouse-local --query \"{$query}\"");
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("An error occurred while loading the schema of the table {$table}: {$process->getErrorOutput()}");
                return;
            }

            $schema = Str::replace("\'", "'", Str::replace("\n", ',', trim($process->getOutput())));

            // Drop the existing table if it already exists in clickhouse server
            $query = "DROP TABLE IF EXISTS {$filename}";
            $process = Process::fromShellCommandline("clickhouse-client --host '{$clickhouseHost}' --secure --user '{$clickhouseUsername}' --password '{$clickhousePassword}' --database '{$clickhouseDatabase}' --query \"{$query}\"");
            $process->setTimeout(null);
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("An error occurred while dropping the table {$table}: {$process->getErrorOutput()}");
                return;
            }
            if ($copy) {

                // Create the table structure in clickhouse server
                $query = "CREATE TABLE IF NOT EXISTS {$filename} ({$schema}) ENGINE = MergeTree() ORDER BY tuple() SETTINGS index_granularity = 8192";
                $process = Process::fromShellCommandline("clickhouse-client --host '{$clickhouseHost}' --secure --user '{$clickhouseUsername}' --password '{$clickhousePassword}' --database '{$clickhouseDatabase}' --query \"{$query}\"");
                $process->setTimeout(null);
                $process->run();

                if (!$process->isSuccessful()) {
                    Log::error("An error occurred while creating the table schema: {$process->getErrorOutput()}");
                    return;
                }

                // Load the data in clickhouse server
                // https://clickhouse.com/docs/en/integrations/s3#remote-insert-using-clickhouse-local
                $query = "INSERT INTO TABLE FUNCTION remoteSecure('{$clickhouseHost}', '{$clickhouseDatabase}.{$filename}', '{$clickhouseUsername}', '{$clickhousePassword}') (*) SELECT * FROM {$s3Out}";
                $process = Process::fromShellCommandline("clickhouse-local --database '{$clickhouseDatabase}' --query \"{$query}\"");
                $process->setTimeout(null);
                $process->run();

                if (!$process->isSuccessful()) {
                    Log::error("An error occurred while loading the table data: {$process->getErrorOutput()}");
                    return;
                }
            } else {

                // Create a view over the Parquet file in clickhouse server
                $s3Engine = Str::replace('s3(', 'S3(', $s3Out);
                $query = "CREATE TABLE IF NOT EXISTS {$filename} ({$schema}) ENGINE = {$s3Engine}";
                $process = Process::fromShellCommandline("clickhouse-client --host '{$clickhouseHost}' --secure --user '{$clickhouseUsername}' --password '{$clickhousePassword}' --database '{$clickhouseDatabase}' --query \"{$query}\"");
                $process->setTimeout(null);
                $process->run();

                if (!$process->isSuccessful()) {
                    Log::error("An error occurred while creating a view over the table {$table}: {$process->getErrorOutput()}");
                    return;
                }
            }

            // TODO : create view in clickhouse server

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
