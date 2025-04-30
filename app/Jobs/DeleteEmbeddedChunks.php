<?php

namespace App\Jobs;

use App\Helpers\ApiUtilsFacade as ApiUtils;
use App\Models\Chunk;
use App\Models\Collection;
use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteEmbeddedChunks implements ShouldQueue
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
        File::where('is_deleted', true)
            ->get()
            ->each(function (File $file) {
                if ($file->chunks()->count() <= 0) {
                    $file->delete(); // when all chunks have been deleted, delete the file
                } else {
                    $file->chunks()->update(['is_deleted' => true]); // mark chunks as "to be deleted"
                }
            });
        Collection::where('is_deleted', true)
            ->get()
            ->each(function (Collection $collection) {
                try {
                    $response = ApiUtils::delete_collection($collection->name);
                    if ($response['error']) {
                        Log::error($response['error_details']);
                    } else {
                        $collection->delete(); // cascade delete on files and chunks
                    }
                } catch (\Exception $exception) {
                    Log::error($exception->getMessage());
                }
            });
        Collection::where('is_deleted', false)
            ->get()
            ->each(function (Collection $collection) {

                // delete chunks that have not been sent to the vector database
                $collection->chunks()
                    ->where('is_embedded', false)
                    ->where('is_deleted', true)
                    ->delete();

                // delete vectors then delete chunks
                $collection->chunks()
                    ->where('is_embedded', true)
                    ->where('is_deleted', true)
                    ->chunk(500, function ($chunks) use ($collection) {

                        $files = [];
                        $uids = [];

                        foreach ($chunks as $chunk) {
                            if (!in_array($chunk->file_id, $files)) {
                                $files[] = $chunk->file_id;
                            }
                            $uids[] = (string)$chunk->id;
                        }
                        try {
                            $response = ApiUtils::delete_chunks($uids, $collection->name);
                            if ($response['error']) {
                                Log::error($response['error_details']);
                            } else {

                                Chunk::whereIn('id', $uids)->delete();

                                foreach ($files as $fileId) {
                                    if (!Chunk::where('file_id', $fileId)->exists()) {
                                        File::where('id', $fileId)->update(['is_embedded' => false]);
                                    }
                                }
                            }
                        } catch (\Exception $exception) {
                            Log::error($exception->getMessage());
                        }
                    });
            });
    }
}
