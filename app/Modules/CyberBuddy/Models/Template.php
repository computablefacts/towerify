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
 * @property array template
 * @property boolean readonly
 * @property int created_by
 */
class Template extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'cb_templates';

    protected $fillable = [
        'name',
        'template',
        'readonly',
        'created_by',
    ];

    protected $casts = [
        'template' => 'array',
        'readonly' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
