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
 * @property string username
 * @property string directory
 * @property string shell
 * @property string ssh_key
 * @property string action
 */
class VUserSshKey extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_user_ssh_keys';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'username',
        'directory',
        'shell',
        'ssh_key',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
