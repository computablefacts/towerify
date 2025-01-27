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
 * @property string pid
 * @property string entry_host
 * @property string entry_timestamp
 * @property string entry_terminal
 * @property string entry_type
 * @property string entry_username
 * @property string action
 */
class VLoginAndLogout extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_logins_and_logouts';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'pid',
        'entry_host',
        'entry_timestamp',
        'entry_terminal',
        'entry_type',
        'entry_username',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
