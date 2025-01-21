<?php

namespace App\Modules\CyberBuddy\Models;

use App\Modules\CyberBuddy\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string thread_id
 * @property string dom
 * @property boolean autosaved
 * @property int created_by
 */
class Conversation extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_conversations';

    protected $fillable = [
        'thread_id',
        'dom',
        'autosaved',
        'created_by',
    ];

    protected $casts = [
        'autosaved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
