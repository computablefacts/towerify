<?php

namespace App\Modules\CyberBuddy\Jobs;

use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Models\Chunk;
use App\Modules\CyberBuddy\Models\ChunkCollection;
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
        ChunkCollection::all()
            ->each(function (ChunkCollection $collection) {
                $collection->chunks()
                    ->where('is_embedded', false)
                    ->where('is_deleted', false)
                    ->chunk(100, function ($chunks) use ($collection) {

                        $chunkz = [];

                        foreach ($chunks as $chunk) {
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
                            }
                        } catch (\Exception $exception) {
                            Log::error($exception->getMessage());
                        }
                    });
            });
    }
}
