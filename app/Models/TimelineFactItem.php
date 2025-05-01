<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property integer item_id
 * @property integer fact_id
 */
class TimelineFactItem extends Model
{
    protected $table = 't_facts_items';

    protected $fillable = [
        'item_id',
        'fact_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}