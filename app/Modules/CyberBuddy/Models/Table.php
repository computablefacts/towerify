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
 * @property string name
 * @property string description
 * @property boolean copied
 * @property boolean deduplicated
 * @property string last_error
 * @property int created_by
 * @property Carbon started_at
 * @property Carbon finished_at
 */
class Table extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_tables';

    protected $fillable = [
        'name',
        'description',
        'copied',
        'deduplicated',
        'created_by',
        'last_error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'copied' => 'boolean',
        'deduplicated' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
