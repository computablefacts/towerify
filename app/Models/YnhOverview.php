<?php

namespace App\Models;

use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int monitored_ips
 * @property int monitored_dns
 * @property int collected_metrics
 * @property int collected_events
 * @property int vulns_high
 * @property int vulns_high_unverified
 * @property int vulns_medium
 * @property int vulns_low
 * @property int monitored_servers
 */
class YnhOverview extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'ynh_overview';

    protected $fillable = [
        'monitored_ips',
        'monitored_dns',
        'collected_metrics',
        'collected_events',
        'vulns_high',
        'vulns_high_unverified',
        'vulns_medium',
        'vulns_low',
        'monitored_servers',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
