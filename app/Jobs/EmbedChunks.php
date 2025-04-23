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

class EmbedChunks implements ShouldQueue
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
        Collection::where('is_deleted', false)
            ->get()
            ->each(function (Collection $collection) {
                $collection->chunks()
                    ->where('is_embedded', false)
                    ->where('is_deleted', false)
                    ->chunk(500, function ($chunks) use ($collection) {

                        $files = [];
                        $chunkz = [];

                        foreach ($chunks as $chunk) {
                            if (!in_array($chunk->file_id, $files)) {
                                $files[] = $chunk->file_id;
                            }
                            $chunkz[] = [
                                'uid' => (string)$chunk->id,
                                'text' => $chunk->text,
                                'tags' => $chunk->tags()->pluck('tag')->toArray(),
                            ];
                        }
                        try {
                            $response = ApiUtils::import_chunks($chunkz, $collection->name);
                            if ($response['error']) {
                                Log::error($response['error_details']);
                            } else {

                                Chunk::whereIn('id', collect($chunkz)->pluck('uid')->toArray())
                                    ->update(['is_embedded' => true]);

                                foreach ($files as $fileId) {
                                    if (!Chunk::where('file_id', $fileId)->where('is_embedded', false)->exists()) {
                                        File::where('id', $fileId)->update(['is_embedded' => true]);
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
