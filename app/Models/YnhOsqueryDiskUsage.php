<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YnhOsqueryDiskUsage extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_disk_usage';

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
