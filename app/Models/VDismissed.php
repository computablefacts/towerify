<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int ynh_server_id
 * @property string name
 * @property Carbon calendar_time
 * @property ?string columns_uid
 */
class VDismissed extends Model
{
    protected $table = 'v_dismissed';

    protected $fillable = [
        'ynh_server_id',
        'name',
        'action',
        'columns_uid',
        'calendar_time',
    ];

    protected $casts = [
        'calendar_time' => 'datetime',
    ];
}
