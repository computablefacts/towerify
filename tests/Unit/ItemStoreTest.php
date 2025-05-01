<?php

namespace Tests\Unit;

use App\Models\TimelineItem;
use Carbon\Carbon;
use Tests\TestCase;

class ItemStoreTest extends TestCase
{
    public function testCreateTimelineItem()
    {
        $item = TimelineItem::createItem($this->user->id, 'webpage', Carbon::now(), 0, [
            'id' => 1234567890,
            'url' => 'https://www.google.com',
            'title' => 'Google',
            'crawled' => true,
        ]);

        $this->assertEquals(4, $item->facts()->count());
        $this->assertEquals([
            'id' => 1234567890,
            'url' => 'https://www.google.com',
            'title' => 'Google',
            'crawled' => true,
        ], $item->attributes());

        // fetch
        $items = TimelineItem::fetchItems($this->user->id, 'webpage');

        $this->assertEquals(1, $items->count());

        // number
        $items = TimelineItem::fetchItems($this->user->id, 'webpage', null, null, null, [
            [['id', '=', 1234567890]]
        ]);

        $this->assertEquals(1, $items->count());

        // boolean
        $items = TimelineItem::fetchItems($this->user->id, 'webpage', null, null, null, [
            [['crawled', '=', true]]
        ]);

        $this->assertEquals(1, $items->count());

        // string (equal)
        $items = TimelineItem::fetchItems($this->user->id, 'webpage', null, null, null, [
            [['title', '=', 'Google']]
        ]);

        $this->assertEquals(1, $items->count());

        // string (like)
        $items = TimelineItem::fetchItems($this->user->id, 'webpage', null, null, null, [
            [['url', 'like', '%www.google.com']]
        ]);

        $this->assertEquals(1, $items->count());

        // equal OR like
        $items = TimelineItem::fetchItems($this->user->id, 'webpage', null, null, null, [
            [
                ['title', '=', 'Google'],
                ['url', 'like', '%www.google.com'],
            ]
        ]);

        $this->assertEquals(1, $items->count());

        // equal AND like
        $items = TimelineItem::fetchItems($this->user->id, 'webpage', null, null, null, [
            [['title', '=', 'Google']],
            [['url', 'like', '%www.google.com']],
        ]);

        $this->assertEquals(1, $items->count());
    }
}
