<?php

namespace App\Models;

use App\Enums\SshTraceStateEnum;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
