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
 * @property string address
 * @property string broadcast
 * @property string interface
 * @property string mask
 * @property string point_to_point
 * @property string type
 * @property string actions
 */
class VNetworkInterfaces extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_network_interfaces';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'address',
        'broadcast',
        'interface',
        'mask',
        'point_to_point',
        'type',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
