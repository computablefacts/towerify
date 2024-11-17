<?php

namespace App\Modules\Federa\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\Federa\Events\IngestFile;
use App\Modules\Federa\Models\Collection;
use App\Modules\Federa\Models\CsvFile;
use App\Modules\Federa\Rules\IsValidCollectionName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IngestFileListener extends AbstractListener
{
    protected function handle2($event)
    {
        if (!($event instanceof IngestFile)) {
            throw new \Exception('Invalid event type!');
        }
        if (!IsValidCollectionName::test($event->collection)) {
            Log::error("Invalid collection name : {$event->collection}");
            return;
        }

        Auth::login($event->user); // otherwise the tenant will not be properly set

        try {
            /** @var ?Collection $collection */
            $collection = Collection::where('name', $event->collection)
                ->where('is_deleted', false)
                ->first();

            if (!$collection) {
                /** @var Collection $collection */
                $collection = Collection::create(['name' => $event->collection]);
            }

            /** @var CsvFile $file */
            $file = CsvFile::find($event->fileId);

            if (!$file) {
                throw new \Exception("Invalid file id : {$event->fileId}");
            }

            // TODO : extract headers
            // TODO : normalize headers
            // TODO : check provided types OR infer column types
            // TODO : import rows

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
