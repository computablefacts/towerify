<?php

namespace App\Modules\AdversaryMeter\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int honeypot_id
 * @property ?int attacker_id
 * @property string event
 * @property string uid
 * @property string endpoint
 * @property Carbon timestamp
 * @property string request_uri
 * @property string user_agent
 * @property string ip
 * @property string details
 * @property string feed_name
 * @property ?string hosting_service_description
 * @property ?string hosting_service_registry
 * @property ?string hosting_service_asn
 * @property ?string hosting_service_cidr
 * @property ?string hosting_service_country_code
 * @property ?string hosting_service_date
 * @property bool human
 * @property bool targeted
 */
class HoneypotEvent extends Model
{
    use HasFactory;

    protected $table = 'honeypots_events';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'honeypot_id',
        'attacker_id',
        'event',
        'uid',
        'human',
        'endpoint',
        'timestamp',
        'request_uri',
        'user_agent',
        'ip',
        'details',
        'targeted',
        'feed_name',
        'hosting_service_description',
        'hosting_service_registry',
        'hosting_service_asn',
        'hosting_service_cidr',
        'hosting_service_country_code',
        'hosting_service_date',
    ];

    protected $casts = [
        'human' => 'boolean',
        'targeted' => 'boolean',
        'timestamp' => 'datetime',
    ];
}
