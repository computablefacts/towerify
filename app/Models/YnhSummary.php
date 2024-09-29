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
 */
class YnhSummary extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'ynh_summaries';

    protected $fillable = [
        'monitored_ips',
        'monitored_dns',
        'collected_metrics',
        'collected_events',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
