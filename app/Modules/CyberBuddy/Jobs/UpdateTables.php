<?php

namespace App\Modules\CyberBuddy\Jobs;

use App\Modules\CyberBuddy\Events\ImportTable;
use App\Modules\CyberBuddy\Helpers\ClickhouseUtils;
use App\Modules\CyberBuddy\Models\Table;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UpdateTables implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public function handle()
    {
        Table::where('updatable', true)
            ->get()
            ->each(function (Table $table) {

                Auth::loginUsingId($table->id);

                $user = Auth::user();
                $region = $table->credentials['region'];
                $accessKeyId = $table->credentials['access_key_id'];
                $secretAccessKey = $table->credentials['secret_access_key'];
                $inputFolder = $table->credentials['input_folder'];
                $outputFolder = $table->credentials['output_folder'];

                $s3Client = new \Aws\S3\S3Client([
                    'region' => $region,
                    'version' => 'latest',
                    'credentials' => [
                        'key' => $accessKeyId,
                        'secret' => $secretAccessKey,
                    ],
                ]);

                $bucket = explode('/', $inputFolder, 2)[0];
                $prefix = isset(explode('/', $inputFolder, 2)[1]) ? explode('/', $inputFolder, 2)[1] : '';
                $objects = $s3Client->listObjectsV2([
                    'Bucket' => $bucket,
                    'Prefix' => $prefix,
                ]);

                collect($objects['Contents'] ?? [])
                    ->filter(function (array $object) use ($table) {
                        return ClickhouseUtils::normalizeTableName($object['Key']) === $table->name &&
                            (!$table->finished_at || Carbon::createFromTimestamp($object['LastModified']->getTimestamp())->isAfter($table->finished_at));
                    })
                    ->each(function (array $object) use ($user, $region, $accessKeyId, $secretAccessKey, $inputFolder, $outputFolder, $table) {

                        $copy = $table->copied;
                        $deduplicate = $table->deduplicated;
                        $updatable = $table->updatable;
                        $description = $table->description;
                        $columns = $table->schema;

                        ImportTable::dispatch($user, $region, $accessKeyId, $secretAccessKey, $inputFolder, $outputFolder, $copy, $deduplicate, $updatable, $object['Key'], $columns, $description);
                    });
            });

        // TODO : update dependencies
    }
}
