<?php

namespace App\Modules\CyberBuddy\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\CyberBuddy\Events\ImportVirtualTable;
use App\Modules\CyberBuddy\Helpers\ClickhouseClient;
use App\Modules\CyberBuddy\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportVirtualTableListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof ImportVirtualTable)) {
            throw new \Exception('Invalid event type!');
        }

        $user = $event->user;
        $table = $event->table;
        $query = $event->query;
        $description = $event->description;

        Auth::login($user); // otherwise the tenant will not be properly set

        $tableName = Str::replace(['-', ' '], '_', Str::lower(Str::beforeLast(Str::afterLast($table, '/'), '.')));
        $tbl = Table::updateOrCreate([
            'name' => $tableName,
            'created_by' => $user->id,
        ], [
            'name' => $tableName,
            'description' => $description,
            'copied' => true,
            'deduplicated' => false,
            'last_error' => null,
            'started_at' => Carbon::now(),
            'finished_at' => null,
            'created_by' => $user->id,
        ]);

        try {

            // Instead of dropping the existing table, create a temporary table and fill it
            // Then, drop the existing table and rename the temporary table
            $uid = Str::random(10);

            // Create the table in clickhouse server
            $query = "CREATE TABLE {$tableName}_{$uid} AS {$query}";
            $output = ClickhouseClient::executeQuery($query);

            if (!$output) {
                $tbl->last_error = 'Error #10';
                $tbl->save();
                return;
            }

            // Drop any existing table from clickhouse server
            $output = ClickhouseClient::dropTableIfExists($tableName);

            if (!$output) {
                $tbl->last_error = 'Error #11';
                $tbl->save();
                return;
            }

            // Rename the newly created table with the old name
            $output = ClickhouseClient::renameTable("{$tableName}_{$uid}", $tableName);

            if (!$output) {
                $tbl->last_error = 'Error #12';
                $tbl->save();
                return;
            }

            $tbl->last_error = null;
            $tbl->finished_at = Carbon::now();
            $tbl->save();

            // TODO : create tmp_* view in clickhouse server for backward compatibility

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
