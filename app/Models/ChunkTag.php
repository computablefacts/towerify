<?php

namespace App\Models;

use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int chunk_id
 * @property string tag
 * @property int created_by
 */
class ChunkTag extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_chunks_tags';

    protected $fillable = [
        'chunk_id',
        'tag',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
