<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int ynh_server_id
 * @property Carbon timestamp
 * @property double time_spent_on_system_workloads_pct
 * @property double time_spent_on_user_workloads_pct
 * @property double time_spent_idle_pct
 */
class YnhOsqueryProcessorUsage extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_processor_usage';

    protected $fillable = [
        'ynh_server_id',
        'timestamp',
        'system_workloads_pct',
        'user_workloads_pct',
        'idle_pct',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }
}
