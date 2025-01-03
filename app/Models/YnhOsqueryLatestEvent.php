<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon calendar_time
 * @property int ynh_osquery_id
 * @property int ynh_server_id
 * @property string event_name
 * @property string server_name
 * @property bool updated
 */
class YnhOsqueryLatestEvent extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_latest_events';

    protected $fillable = [
        'calendar_time',
        'event_name',
        'server_name',
        'ynh_osquery_id',
        'ynh_server_id',
        'updated',
    ];

    protected $casts = [
        'calendar_time' => 'datetime',
        'updated' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
