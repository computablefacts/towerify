<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon date_min
 * @property Carbon date_max
 * @property int ynh_server_id
 * @property int count
 * @property array events
 */
class YnhOsqueryEventsCount extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_events_counts';

    protected $fillable = [
        'date_min',
        'date_max',
        'ynh_server_id',
        'count',
        'events'
    ];

    protected $casts = [
        'date_min' => 'datetime',
        'date_max' => 'datetime',
        'events' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
