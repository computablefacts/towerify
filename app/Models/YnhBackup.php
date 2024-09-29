<?php

namespace App\Models;

use App\Traits\HasTenant2;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property ?int user_id
 * @property int ynh_server_id
 * @property string name
 * @property int size
 * @property ?string storage_path
 * @property array result
 */
class YnhBackup extends Model
{
    use HasFactory, HasTenant2;

    protected $table = 'ynh_backups';

    protected $fillable = [
        'ynh_server_id',
        'user_id',
        'name',
        'size',
        'storage_path',
        'result'
    ];

    protected $casts = [
        'result' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'ynh_server_id', 'id');
    }
}
