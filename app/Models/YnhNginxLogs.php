<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YnhNginxLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_ynh_server_id',
        'to_ynh_server_id',
        'from_ip_address',
        'to_ip_address',
        'service',
        'weight',
        'updated',
    ];

    protected $casts = [
        'updated' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'from_ynh_server_id', 'id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'to_ynh_server_id', 'id');
    }
}
