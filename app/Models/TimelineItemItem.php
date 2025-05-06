<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string type
 * @property integer from_item_id
 * @property integer to_item_id
 */
class TimelineItemItem extends Model
{
    protected $table = 't_items_items';

    protected $fillable = [
        'type',
        'from_item_id',
        'to_item_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function from(): HasOne
    {
        return $this->hasOne(TimelineItem::class, 'id', 'from_item_id');
    }

    public function to(): HasOne
    {
        return $this->hasOne(TimelineItem::class, 'id', 'to_item_id');
    }
}