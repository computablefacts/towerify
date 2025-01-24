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
 * @property string user
 * @property string group
 * @property string username
 * @property string home_directory
 * @property string default_shell
 * @property string action
 */
class VUserAccounts extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_user_accounts';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'user',
        'group',
        'username',
        'home_directory',
        'default_shell',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
