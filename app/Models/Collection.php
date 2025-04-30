<?php

namespace App\Models;

use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property integer priority
 * @property boolean is_deleted
 * @property int created_by
 */
class Collection extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_collections';

    protected $fillable = [
        'name',
        'priority',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class, 'collection_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'collection_id', 'id');
    }
}
