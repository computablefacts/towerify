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
 * @property string path
 * @property string local_address
 * @property string local_port
 * @property string action
 */
class VProcessWithBoundNetworkSockets extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_processes_with_bound_network_sockets';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'path',
        'local_address',
        'local_port',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
