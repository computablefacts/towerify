<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property integer owned_by
 * @property string type
 * @property Carbon timestamp
 * @property integer flags
 */
class TimelineItem extends Model
{
    const int FLAG_DELETED = 1;
    const int FLAG_HIDDEN = 2;

    const string REF_RESCHEDULED = 'rescheduled';
    const string REF_SHARED = 'shared';
    const string REF_SNOOZED = 'snoozed';

    protected $table = 't_items';

    protected $fillable = [
        'owned_by',
        'type',
        'timestamp',
        'flags',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function createNote(int $ownedBy, string $body, string $subject = ''): TimelineItem
    {
        return self::createItem($ownedBy, 'note', Carbon::now(), 0, [
            'body' => Str::limit(trim($body), 1000 - 3, '...'),
            'subject' => Str::limit(trim($subject), 1000 - 3, '...'),
        ]);
    }

    public static function fetchNotes(?int $ownedBy = null, ?Carbon $createdAtOrAfter = null, ?Carbon $createdAtOrBefore = null, ?int $flags = null, array $ands = []): \Illuminate\Support\Collection
    {
        return self::fetchItems($ownedBy, 'note', $createdAtOrAfter, $createdAtOrBefore, $flags, $ands);
    }

    public static function createItem(int $ownedBy, string $type, Carbon $timestamp, int $flags = 0, array $attributes = []): TimelineItem
    {
        return DB::transaction(function () use ($ownedBy, $type, $timestamp, $flags, $attributes) {
            /** @var TimelineItem $item */
            $item = TimelineItem::create([
                'owned_by' => $ownedBy,
                'type' => $type,
                'timestamp' => $timestamp,
                'flags' => max($flags, 0),
            ]);
            foreach ($attributes as $attribute => $value) {
                if ($attribute && $value) {
                    $item->addAttribute($attribute, $value);
                }
            }
            return $item;
        });
    }

    public static function fetchItems(?int $ownedBy = null, ?string $type = null, ?Carbon $createdAtOrAfter = null, ?Carbon $createdAtOrBefore = null, ?int $flags = null, array $ands = []): \Illuminate\Support\Collection
    {
        $query = TimelineItem::select('t_items.*');
        if ($ownedBy) {
            $query->where('t_items.owned_by', $ownedBy);
        }
        if (!empty($type)) {
            $query->where('t_items.type', $type);
        }
        if ($createdAtOrAfter) {
            $query->where('t_items.timestamp', '>=', $createdAtOrAfter);
        }
        if ($createdAtOrBefore) {
            $query->where('t_items.timestamp', '<=', $createdAtOrBefore);
        }
        if ($flags !== null) {
            if ($flags > 0) {
                $query->whereRaw("(t_items.flags & {$flags}) > 0"); // only returns items with some flags set
            } else {
                $query->whereRaw("t_items.flags = 0"); // only returns items with no flags set
            }
        }
        if (count($ands) > 0) {

            $id = 0;

            /** @var array $ors */
            foreach ($ands as $ors) {
                /** @var array $or */
                foreach ($ors as $or) {
                    $id++;
                    $query
                        ->join("t_facts_items AS tfi{$id}", "tfi{$id}.item_id", '=', 't_items.id')
                        ->join("t_facts AS tf{$id}", "tf{$id}.id", '=', "tfi{$id}.fact_id");
                }
            }

            $id = 0;

            /** @var array $ors */
            foreach ($ands as $ors) {
                $query->where(function ($query) use ($ors, &$id) {
                    /** @var array $or */
                    foreach ($ors as $or) {
                        $id++;
                        [$attribute, $operator, $value] = $or;
                        $query->orWhere(function ($query) use ($attribute, $operator, $value, $id) {
                            $query->where("tf{$id}.attribute", $attribute);
                            if (is_string($value)) {
                                $query->where("tf{$id}.type", TimelineFact::TYPE_STRING);
                                if ($operator === 'like' || $operator === 'LIKE') {
                                    $query->whereLike("tf{$id}.value", $value);
                                } else {
                                    $query->where("tf{$id}.value", $operator, $value);
                                }
                            } else if (is_bool($value)) {
                                $query->where("tf{$id}.type", TimelineFact::TYPE_BOOLEAN);
                                $query->where("tf{$id}.numerical_value", $operator, $value);
                            } else if ($value instanceof Carbon) {
                                $query->where("tf{$id}.type", TimelineFact::TYPE_TIMESTAMP);
                                $query->where("tf{$id}.numerical_value", $operator, $value->utc()->timestamp);
                            } else {
                                $query->where("tf{$id}.type", TimelineFact::TYPE_NUMBER);
                                $query->where("tf{$id}.numerical_value", $operator, $value);
                            }
                        });
                    }
                });
            }
        }
        return $query->orderByDesc('t_items.timestamp')->distinct()->get();
    }

    public function facts(): BelongsToMany
    {
        return $this->belongsToMany(TimelineFact::class, 't_facts_items', 'item_id', 'fact_id');
    }

    public function owner(): ?User
    {
        return $this->owned_by ? User::where('id', $this->owned_by)->first() : null;
    }

    public function attributes(array $ands = []): array
    {
        return $this->facts()
            ->when($ands, function ($query) use ($ands) {
                /** @var array $ors */
                foreach ($ands as $ors) {
                    $query->where(function ($query) use ($ors) {
                        /** @var array $or */
                        foreach ($ors as $or) {
                            [$attribute, $operator, $value] = $or;
                            $query->orWhere(function ($query) use ($attribute, $operator, $value) {
                                $query->where('attribute', $attribute);
                                if (is_string($value)) {
                                    $query->where('type', TimelineFact::TYPE_STRING);
                                    $query->where('value', $operator, $value);
                                } else if (is_bool($value)) {
                                    $query->where('type', TimelineFact::TYPE_BOOLEAN);
                                    $query->where('numerical_value', $operator, $value);
                                } else if ($value instanceof Carbon) {
                                    $query->where('type', TimelineFact::TYPE_TIMESTAMP);
                                    $query->where('numerical_value', $operator, $value->utc()->timestamp);
                                } else {
                                    $query->where('type', TimelineFact::TYPE_NUMBER);
                                    $query->where('numerical_value', $operator, $value);
                                }
                            });
                        }
                    });
                }
                return $query;
            })
            ->orderBy('attribute')
            ->orderByDesc('updated_at')
            ->distinct()
            ->get()
            ->groupBy('attribute')
            ->map(fn($group) => $group->first()->attributeValue()) // if an attribute has been edited, keep only the most recent version of it
            ->toArray();
    }

    public function addAttribute(string $attribute, mixed $value, ?int $ownedBy = null): TimelineFact
    {
        return DB::transaction(function () use ($attribute, $value, $ownedBy) {
            if (is_string($value)) {
                $type = TimelineFact::TYPE_STRING;
                $numericalValue = null;
            } else if (is_bool($value)) {
                $type = TimelineFact::TYPE_BOOLEAN;
                $numericalValue = $value === true ? 1.0 : 0.0;
                $value = null;
            } else if ($value instanceof Carbon) {
                $type = TimelineFact::TYPE_TIMESTAMP;
                $numericalValue = $value->utc()->timestamp;
                $value = null;
            } else {
                $type = TimelineFact::TYPE_NUMBER;
                $numericalValue = $value;
                $value = null;
            }
            /** @var TimelineFact $fact */
            $fact = TimelineFact::create([
                'owned_by' => $ownedBy ?? $this->owned_by,
                'attribute' => $attribute,
                'type' => $type,
                'value' => $value,
                'numerical_value' => $numericalValue,
            ]);
            $this->facts()->attach($fact);
            return $fact;
        });
    }

    public function updateAttribute(string $attribute, mixed $value, ?int $ownedBy = null): TimelineFact
    {
        return DB::transaction(function () use ($attribute, $value, $ownedBy) {
            if (is_string($value)) {
                $type = TimelineFact::TYPE_STRING;
                $numericalValue = null;
            } else if (is_bool($value)) {
                $type = TimelineFact::TYPE_BOOLEAN;
                $numericalValue = $value === true ? 1.0 : 0.0;
                $value = null;
            } else if ($value instanceof Carbon) {
                $type = TimelineFact::TYPE_TIMESTAMP;
                $numericalValue = $value->utc()->timestamp;
                $value = null;
            } else {
                $type = TimelineFact::TYPE_NUMBER;
                $numericalValue = $value;
                $value = null;
            }
            /** @var TimelineFact $fact */
            $fact = TimelineFact::where('owned_by', $ownedBy ?? $this->owned_by)
                ->where('attribute', $attribute)
                ->where('type', $type)
                ->firstOrFail();
            $fact->update([
                'owned_by' => $ownedBy ?? $this->owned_by,
                'attribute' => $attribute,
                'type' => $type,
                'value' => $value,
                'numerical_value' => $numericalValue,
            ]);
            return $fact;
        });
    }

    public function removeAttribute(string $attribute, ?int $ownedBy = null): void
    {
        DB::transaction(function () use ($attribute, $ownedBy) {
            $this->facts()
                ->where('owned_by', $ownedBy ?? $this->owned_by)
                ->where('attribute', $attribute)
                ->get()
                ->each(function (TimelineFact $fact) {
                    if ($fact->items()->count() > 1) {
                        $this->facts()->detach($fact->id);
                    } else {
                        $fact->delete();
                    }
                });
        });
    }

    public function deleteItem(): void
    {
        $this->flags |= self::FLAG_DELETED;
    }

    public function hideItem(): void
    {
        $this->flags |= self::FLAG_HIDDEN;
    }

    public function restoreItem(): void
    {
        $this->flags &= ~self::FLAG_DELETED;
    }

    public function showItem(): void
    {
        $this->flags &= ~self::FLAG_HIDDEN;
    }

    public function isDeleted(): bool
    {
        return ($this->flags & self::FLAG_DELETED) > 0;
    }

    public function isHidden(): bool
    {
        return ($this->flags & self::FLAG_HIDDEN) > 0;
    }

    public function isSnoozed(): bool
    {
        return $this->children(self::REF_SNOOZED)->isNotEmpty();
    }

    public function snooze(Carbon $date): TimelineItem
    {
        return DB::transaction(function () use ($date) {
            $item = TimelineItem::createItem($this->owned_by, $this->type, $date, $this->flags);
            $this->facts()->get()->each(fn(TimelineFact $fact) => $item->facts()->attach($fact));
            $this->addRelation(self::REF_SNOOZED, $item);
            return $item;
        });
    }

    public function isRescheduled(): bool
    {
        return $this->children(self::REF_RESCHEDULED)->isNotEmpty();
    }

    public function reschedule(Carbon $date): TimelineItem
    {
        return DB::transaction(function () use ($date) {
            $item = TimelineItem::createItem($this->owned_by, $this->type, $date, $this->flags);
            $this->facts()->get()->each(fn(TimelineFact $fact) => $item->facts()->attach($fact));
            $this->addRelation(self::REF_RESCHEDULED, $item);
            return $item;
        });
    }

    public function isShared(): bool
    {
        return $this->children(self::REF_SHARED)->isNotEmpty();
    }

    public function share(int $ownedBy, ?Carbon $date = null): TimelineItem
    {
        return DB::transaction(function () use ($ownedBy, $date) {
            $item = TimelineItem::createItem($ownedBy, $this->type, $date ?? $this->timestamp, $this->flags);
            $this->facts()->get()->each(fn(TimelineFact $fact) => $item->facts()->attach($fact));
            $this->addRelation(self::REF_SHARED, $item);
            return $item;
        });
    }

    private function addRelation(string $type, TimelineItem $item): void
    {
        TimelineItemItem::create([
            'type' => $type,
            'from_item_id' => $this->id,
            'to_item_id' => $item->id,
        ]);
    }

    private function removeRelation(string $type, TimelineItem $item): void
    {
        TimelineItemItem::where('type', $type)
            ->where('from_item_id', $this->id)
            ->where('to_item_id', $item->id)
            ->delete();
    }

    private function parents(?string $type): \Illuminate\Support\Collection
    {
        $query = TimelineItemItem::where('to_item_id', $this->id);

        if ($type) {
            $query = $query->where('type', $type);
        }
        return $query->get()->map(fn(TimelineItemItem $itemItem) => $itemItem->from()->first());
    }

    private function children(?string $type): \Illuminate\Support\Collection
    {
        $query = TimelineItemItem::where('from_item_id', $this->id);

        if ($type) {
            $query = $query->where('type', $type);
        }
        return $query->get()->map(fn(TimelineItemItem $itemItem) => $itemItem->to()->first());
    }
}