<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string name
 * @property boolean is_deleted
 * @property int created_by
 */
class ChunkCollection extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_chunks_collections';

    protected $fillable = [
        'name',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(Chunk::class, 'collection_id', 'id');
    }
}
