<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int collection_id
 * @property int file_id
 * @property ?string url
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
        'file_id',
        'url',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isEmbedded(): bool
    {
        return $this->is_embedded;
    }

    public function collection(): HasOne
    {
        return $this->hasOne(Collection::class, 'id', 'collection_id');
    }

    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ChunkTag::class, 'chunk_id', 'id');
    }
}
