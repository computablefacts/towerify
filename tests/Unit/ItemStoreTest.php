<?php

namespace Tests\Unit;

use App\Models\TimelineItem;
use Carbon\Carbon;
use Tests\TestCase;

class ItemStoreTest extends TestCase
{
    public function testCreateItem()
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
    }

    public function testHideItem()
    {
        $item = TimelineItem::createItem($this->user->id, 'hide_item', Carbon::now());
        $item->hideItem();
        $item->save();

        $this->assertTrue($item->isHidden());
        $this->assertFalse($item->isDeleted());

        $items = TimelineItem::fetchItems($this->user->id, 'hide_item');

        $this->assertEquals(1, $items->count());

        $items = TimelineItem::fetchItems($this->user->id, 'hide_item', null, null, TimelineItem::FLAG_DELETED);

        $this->assertEquals(0, $items->count());

        $items = TimelineItem::fetchItems($this->user->id, 'hide_item', null, null, TimelineItem::FLAG_HIDDEN);

        $this->assertEquals(1, $items->count());

        $items = TimelineItem::fetchItems($this->user->id, 'hide_item', null, null, 0);

        $this->assertEquals(0, $items->count());
    }

    public function testShowItem()
    {
        $item = TimelineItem::createItem($this->user->id, 'hide_item', Carbon::now(), TimelineItem::FLAG_HIDDEN);

        $this->assertTrue($item->isHidden());
        $this->assertFalse($item->isDeleted());

        $item->showItem();
        $item->save();

        $this->assertFalse($item->isHidden());
        $this->assertFalse($item->isDeleted());
    }

    public function testDeleteItem()
    {
        $item = TimelineItem::createItem($this->user->id, 'delete_item', Carbon::now());
        $item->deleteItem();
        $item->save();

        $this->assertFalse($item->isHidden());
        $this->assertTrue($item->isDeleted());

        $items = TimelineItem::fetchItems($this->user->id, 'delete_item');

        $this->assertEquals(1, $items->count());

        $items = TimelineItem::fetchItems($this->user->id, 'delete_item', null, null, TimelineItem::FLAG_HIDDEN);

        $this->assertEquals(0, $items->count());

        $items = TimelineItem::fetchItems($this->user->id, 'delete_item', null, null, TimelineItem::FLAG_DELETED);

        $this->assertEquals(1, $items->count());

        $items = TimelineItem::fetchItems($this->user->id, 'delete_item', null, null, 0);

        $this->assertEquals(0, $items->count());
    }

    public function testRestoreItem()
    {
        $item = TimelineItem::createItem($this->user->id, 'hide_item', Carbon::now(), TimelineItem::FLAG_DELETED);

        $this->assertFalse($item->isHidden());
        $this->assertTrue($item->isDeleted());

        $item->restoreItem();
        $item->save();

        $this->assertFalse($item->isHidden());
        $this->assertFalse($item->isDeleted());
    }

    public function testAddAttribute()
    {
        $item = TimelineItem::createItem($this->user->id, 'add_attribute', Carbon::now());

        $this->assertEquals(0, $item->facts()->count());
        $this->assertEquals([], $item->attributes());

        $item->addAttribute('pi', 3.14);

        $this->assertEquals(1, $item->facts()->count());
        $this->assertEquals(['pi' => 3.14], $item->attributes());
    }

    public function testUpdateAttribute()
    {
        $item = TimelineItem::createItem($this->user->id, 'update_attribute', Carbon::now(), 0, ['pi' => 3.14]);

        $this->assertEquals(1, $item->facts()->count());
        $this->assertEquals(['pi' => 3.14], $item->attributes());

        $item->updateAttribute('pi', 3.14159265359);

        $this->assertEquals(1, $item->facts()->count());
        $this->assertEquals(['pi' => 3.14], $item->attributes());
    }

    public function testRemoveAttribute()
    {
        $item = TimelineItem::createItem($this->user->id, 'remove_attribute', Carbon::now(), 0, ['pi' => 3.14]);

        $this->assertEquals(1, $item->facts()->count());
        $this->assertEquals(['pi' => 3.14], $item->attributes());

        $item->removeAttribute('pi');

        $this->assertEquals(0, $item->facts()->count());
        $this->assertEquals([], $item->attributes());
    }

    public function testFetchItemsWithTypeFilter()
    {
        $item = TimelineItem::createItem($this->user->id, 'webpage_1', Carbon::now(), 0, [
            'id' => 1234567890,
            'url' => 'https://www.google.com',
            'title' => 'Google',
            'crawled' => true,
        ]);

        $items = TimelineItem::fetchItems($this->user->id, 'webpage_1');

        $this->assertEquals(1, $items->count());
    }

    public function testFetchItemsWithTimestampFilters()
    {
        $yesterday = Carbon::yesterday();
        $tomorrow = Carbon::tomorrow();
        $item = TimelineItem::createItem($this->user->id, 'webpage_2', Carbon::now(), 0, [
            'id' => 1234567890,
            'url' => 'https://www.google.com',
            'title' => 'Google',
            'crawled' => true,
        ]);

        // after date
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_2', $yesterday);

        $this->assertEquals(1, $items->count());

        // before date
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_2', null, $tomorrow);

        $this->assertEquals(1, $items->count());

        // after date1 AND before date2
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_2', $yesterday, $tomorrow);

        $this->assertEquals(1, $items->count());
    }

    public function testFetchItemsWithAttributeFilters()
    {
        $item = TimelineItem::createItem($this->user->id, 'webpage_3', Carbon::now(), 0, [
            'id' => 1234567890,
            'url' => 'https://www.google.com',
            'title' => 'Google',
            'crawled' => true,
        ]);

        // number
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_3', null, null, null, [
            [['id', '=', 1234567890]]
        ]);

        $this->assertEquals(1, $items->count());

        // boolean
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_3', null, null, null, [
            [['crawled', '=', true]]
        ]);

        $this->assertEquals(1, $items->count());

        // string (equal)
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_3', null, null, null, [
            [['title', '=', 'Google']]
        ]);

        $this->assertEquals(1, $items->count());

        // string (like)
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_3', null, null, null, [
            [['url', 'like', '%www.google.com']]
        ]);

        $this->assertEquals(1, $items->count());

        // OR
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_3', null, null, null, [
            [
                ['id', '=', 1234567890],
                ['title', '=', 'Google'],
                ['url', 'like', '%www.google.com'],
                ['crawled', '=', true],
            ]
        ]);

        $this->assertEquals(1, $items->count());

        // AND
        $items = TimelineItem::fetchItems($this->user->id, 'webpage_3', null, null, null, [
            [['id', '=', 1234567890]],
            [['title', '=', 'Google']],
            [['url', 'like', '%www.google.com']],
            [['crawled', '=', true]],
        ]);

        $this->assertEquals(1, $items->count());
    }
}
