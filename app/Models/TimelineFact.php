<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property integer owned_by
 * @property string attribute
 * @property string type
 * @property string value
 * @property double numerical_value
 */
class TimelineFact extends Model
{
    const string TYPE_STRING = 'string';
    const string TYPE_NUMBER = 'number';
    const string TYPE_TIMESTAMP = 'timestamp';
    const string TYPE_BOOLEAN = 'boolean';

    protected $table = 't_facts';

    protected $fillable = [
        'owned_by',
        'attribute',
        'type',
        'value',
        'numerical_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(TimelineItem::class, 't_facts_items', 'fact_id', 'item_id');
    }

    public function owner(): ?User
    {
        return $this->owned_by ? User::where('id', $this->owned_by)->first() : null;
    }

    public function attributeName(): string
    {
        return $this->type;
    }

    public function attributeValue(): mixed
    {
        return match ($this->type) {
            self::TYPE_STRING => $this->value,
            self::TYPE_NUMBER => $this->numerical_value,
            self::TYPE_TIMESTAMP => Carbon::createFromTimestampUTC($this->numerical_value),
            self::TYPE_BOOLEAN => $this->numerical_value === 1.0,
            default => null,
        };
    }
}