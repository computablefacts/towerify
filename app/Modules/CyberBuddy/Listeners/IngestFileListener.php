<?php

namespace App\Modules\CyberBuddy\Listeners;

use App\Listeners\AbstractListener;
use App\Modules\CyberBuddy\Events\IngestFile;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Models\Chunk;
use App\Modules\CyberBuddy\Models\ChunkCollection;
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

        $url = $event->url;

        if (!IsValidCollectionName::test($event->collection)) {
            Log::error("Invalid collection name : {$event->collection}");
            return;
        }
        if (!Str::startsWith($url, 'https://')) {
            Log::error("Invalid url : {$url}");
            return;
        }

        Auth::login($event->user); // otherwise the tenant will not be properly set

        try {
            $collection = ChunkCollection::where('name', $event->collection)->first();

            if (!$collection) {
                $collection = ChunkCollection::create([
                    'name' => $event->collection,
                ]);
            }

            $response = ApiUtils::file_input($event->user->client(), $url);

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
                        'file' => $url,
                        'page' => $page,
                        'text' => $text,
                    ]);

                    foreach ($tags as $tag) {
                        $chunk->tags()->create(['tag' => $tag]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
