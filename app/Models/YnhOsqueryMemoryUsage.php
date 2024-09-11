<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int ynh_server_id
 * @property Carbon timestamp
 * @property double percent_available
 * @property double percent_used
 * @property double space_left_gb
 * @property double total_space_gb
 * @property double used_space_gb
 */
class YnhOsqueryMemoryUsage extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_memory_usage';

    protected $fillable = [
        'ynh_server_id',
        'timestamp',
        'percent_available',
        'percent_used',
        'space_left_gb',
        'total_space_gb',
        'used_space_gb',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }
}
