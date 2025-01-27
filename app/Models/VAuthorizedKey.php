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
 * @property string key_file
 * @property string key
 * @property string key_comment
 * @property string algorithm
 * @property string action
 */
class VAuthorizedKey extends Model
{
    use HasFactory, IsView;

    protected $table = 'v_authorized_keys';

    protected $fillable = [
        'user_id',
        'customer_id',
        'tenant_id',
        'event_id',
        'server_id',
        'server_name',
        'server_ip_address',
        'timestamp',
        'key_file',
        'key',
        'key_comment',
        'algorithm',
        'action',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}
