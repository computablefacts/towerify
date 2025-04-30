<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int port_id
 * @property string tag
 */
class PortTag extends Model
{
    use HasFactory;

    protected $table = 'am_ports_tags';

    protected $fillable = [
        'port_id',
        'tag',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
