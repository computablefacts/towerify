<?php

namespace App\Modules\CyberBuddy\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\CyberBuddy\Events\ImportTable;
use App\Modules\CyberBuddy\Helpers\ClickhouseClient;
use App\Modules\CyberBuddy\Helpers\ClickhouseLocal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            $tableName = Str::replace(['-', ' '], '_', Str::lower(Str::beforeLast(Str::afterLast($table, '/'), '.')));
            $bucket = explode('/', $inputFolder, 2)[0];
            $s3In = "s3('https://s3.{$region}.amazonaws.com/{$bucket}/{$table}', '{$accessKeyId}', '{$secretAccessKey}', 'TabSeparatedWithNames')";
            $s3Out = "s3('https://s3.{$region}.amazonaws.com/{$outputFolder}{$tableName}.parquet', '{$accessKeyId}', '{$secretAccessKey}', 'Parquet')";
            $colNames = collect($columns)->map(fn(array $column) => "{$column['old_name']} AS {$column['new_name']}")->join(",");
            $distinct = $deduplicate ? "DISTINCT" : "";

            Log::debug("Importing table {$table} from S3 to clickhouse server");
            Log::debug("Input file: {$s3In}");
            Log::debug("Output file: {$s3Out}");

            // Transform the TSV file to a Parquet file and write it to the user-defined output directory
            $query = "INSERT INTO FUNCTION {$s3Out} SELECT {$distinct} {$colNames} FROM {$s3In} SETTINGS s3_create_new_file_on_insert=1";
            $output = ClickhouseLocal::executeQuery($query);

            if (!$output) {
                return;
            }

            // Get the table schema from the parquet file
            $query = "DESCRIBE TABLE {$s3Out}";
            $output = ClickhouseLocal::executeQuery($query);

            if (!$output) {
                return;
            }

            $schema = Str::replace("\'", "'", Str::replace("\n", ',', $output));

            if ($copy) {

                // Instead of dropping the existing table, create a temporary table and fill it
                // Then, drop the existing table and rename the temporary table
                $uid = Str::random(10);

                // Create the table structure in clickhouse server
                $query = "CREATE TABLE IF NOT EXISTS {$tableName}_{$uid} ({$schema}) ENGINE = MergeTree() ORDER BY tuple() SETTINGS index_granularity = 8192";
                $output = ClickhouseClient::executeQuery($query);

                if (!$output) {
                    return;
                }

                // Load the data in clickhouse server
                // https://clickhouse.com/docs/en/integrations/s3#remote-insert-using-clickhouse-local
                $query = "INSERT INTO TABLE FUNCTION remoteSecure('{$clickhouseHost}', '{$clickhouseDatabase}.{$tableName}_{$uid}', '{$clickhouseUsername}', '{$clickhousePassword}') (*) SELECT * FROM {$s3Out}";
                $output = ClickhouseLocal::executeQuery($query);

                if (!$output) {
                    return;
                }

                // Drop any existing table from clickhouse server
                $output = ClickhouseClient::dropTableIfExists($tableName);

                if (!$output) {
                    return;
                }

                // Rename the newly created table with the old name
                $output = ClickhouseClient::renameTable("{$tableName}_{$uid}", $tableName);

            } else {

                // Drop any existing table from clickhouse server
                $output = ClickhouseClient::dropTableIfExists($tableName);

                if (!$output) {
                    return;
                }

                // Create a view over the Parquet file in clickhouse server
                $s3Engine = Str::replace('s3(', 'S3(', $s3Out);
                $query = "CREATE TABLE IF NOT EXISTS {$tableName} ({$schema}) ENGINE = {$s3Engine}";
                $output = ClickhouseClient::executeQuery($query);
            }

            // TODO : create view in clickhouse server

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
