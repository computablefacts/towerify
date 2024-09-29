<?php

namespace App\Modules\AdversaryMeter\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int port_id
 * @property string png
 */
class Screenshot extends Model
{
    use HasFactory;

    protected $table = 'am_screenshots';

    protected $fillable = [
        'port_id',
        'png',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
