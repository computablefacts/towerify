<?php

namespace App\Modules\CyberBuddy\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\CyberBuddy\Events\IngestFile;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Models\Chunk;
use App\Modules\CyberBuddy\Models\Collection;
use App\Modules\CyberBuddy\Models\File;
use App\Modules\CyberBuddy\Rules\IsValidCollectionName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            $collection = Collection::where('name', $event->collection)
                ->where('is_deleted', false)
                ->first();

            if (!$collection) {
                $collection = Collection::create(['name' => $event->collection]);
            }

            /** @var File $file */
            $file = File::find($event->fileId);

            if (!$file) {
                Log::error("Invalid file id : {$event->fileId}");
            } else {

                $response = ApiUtils::file_input($event->user->client(), $file->downloadUrl());

                if ($response['error']) {
                    Log::error($response['error_details']);
                } else {

                    $fragments = $response['response'];

                    foreach ($fragments as $fragment) {

                        $tags = explode('>', $fragment['metadata']['title']);
                        $page = $fragment['metadata']['page_idx'] + 1;

                        if ($fragment['metadata']['tag'] === 'list') {
                            $text = trim($fragment['metadata']['prevPara']['text']) . "\n" . trim($fragment['text']);
                        } else {
                            $text = trim($fragment['text']);
                        }

                        /** @var Chunk $chunk */
                        $chunk = $collection->chunks()->create([
                            'file_id' => $file->id,
                            'url' => $file->downloadUrl(),
                            'page' => $page,
                            'text' => $text,
                        ]);

                        foreach ($tags as $tag) {
                            $chunk->tags()->create(['tag' => Str::lower($tag)]);
                        }
                    }
                    if (!Chunk::where('file_id', $file->id)->exists()) { // no chunks -> no embeddings -> processing is complete
                        $file->is_embedded = true;
                        $file->save();
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
