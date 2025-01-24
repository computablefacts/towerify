<?php

namespace App\Models;

use App\Traits\IsView;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int user_id
 * @property int customer_id
 * @property int tenant_id
 * @property int event_id
 * @property int server_id
 * @property string server_name
 * @property string server_ip_address
 * @property Carbon timestamp
 * @property string file
 * @property string command
 * @property string last_run_time
 * @property string next_run_time
 * @property string cron
 * @property string enabled
 * @property string action
 */
class VScheduledTasks extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_scheduled_tasks';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'file',
        'command',
        'last_run_time',
        'next_run_time',
        'cron',
        'enabled',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
