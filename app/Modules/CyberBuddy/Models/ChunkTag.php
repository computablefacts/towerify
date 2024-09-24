<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
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
}
