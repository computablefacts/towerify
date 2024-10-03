<?php

namespace App\Modules\CyberBuddy\Jobs;

use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Models\Chunk;
use App\Modules\CyberBuddy\Models\ChunkCollection;
use App\Modules\CyberBuddy\Models\File;
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
        ChunkCollection::where('is_deleted', true)
            ->get()
            ->each(function (ChunkCollection $collection) {
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
        ChunkCollection::where('is_deleted', false)
            ->get()
            ->each(function (ChunkCollection $collection) {
                if ($collection->chunks()->count() <= 0) {
                    $collection->delete(); // when all chunks have been deleted, delete both the collection and its files
                } else {

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

                            $uids = [];

                            foreach ($chunks as $chunk) {
                                $uids[] = (string)$chunk->id;
                            }
                            try {
                                $response = ApiUtils::delete_chunks($uids, $collection->name);
                                if ($response['error']) {
                                    Log::error($response['error_details']);
                                } else {
                                    Chunk::whereIn('id', $uids)->delete();
                                }
                            } catch (\Exception $exception) {
                                Log::error($exception->getMessage());
                            }
                        });
                }
            });
    }
}
