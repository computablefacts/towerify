<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int collection_id
 * @property ?string file
 * @property ?int page
 * @property string text
 * @property boolean is_embedded
 * @property boolean is_deleted
 * @property int created_by
 */
class Chunk extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_chunks';

    protected $fillable = [
        'collection_id',
        'file',
        'page',
        'text',
        'is_embedded',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'page' => 'integer',
        'is_embedded' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function tags(): HasMany
    {
        return $this->hasMany(ChunkTag::class, 'chunk_id', 'id');
    }
}
