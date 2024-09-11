<?php

namespace App\Models;

use App\Enums\SshTraceStateEnum;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property ?int user_id
 * @property int ynh_server_id
 * @property string uid
 * @property int order
 * @property SshTraceStateEnum state
 * @property string trace
 */
class YnhSshTraces extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uid',
        'order',
        'state',
        'trace',
        'ynh_server_id',
    ];

    protected $casts = [
        'state' => SshTraceStateEnum::class
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
