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
 * @property ?string uid
 * @property ?string type
 * @property ?string title
 * @property int created_by
 */
class HiddenAlert extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'am_hidden_alerts';

    protected $fillable = [
        'uid',
        'type',
        'title',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
