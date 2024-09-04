<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
