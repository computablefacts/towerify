<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class YnhPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ynh_user_id',
        'ynh_application_id',
        'updated',
    ];

    protected $casts = [
        'updated' => 'boolean',
    ];

    public static function apps(User $user): Collection
    {
        $users = YnhUser::from($user)->map(fn(YnhUser $ynhUser) => $ynhUser->id)->all();
        return YnhPermission::whereIn('ynh_user_id', $users)
            ->whereNotIn('name', ['sftp.main', 'ssh.main'])
            ->get()
            ->sortBy([
                ['application.name', 'asc']
            ]);
    }

    public static function currentPermissions(User $user): Collection
    {
        return YnhServer::forUser($user)
            ->flatMap(function (YnhServer $server) use ($user) {
                return $server->currentPermissions($user)
                    ->map(function (string $permission) use ($server) {
                        return (object)[
                            'ynh_user_id' => null,
                            'server_id' => $server->id,
                            'server_name' => $server->name,
                            'server_ip_address' => $server->ip_address,
                            'permission' => $permission,
                        ];
                    });
            })
            ->unique(function ($item) {
                return $item->server_id . $item->permission;
            })
            ->sortBy([
                ['permission', 'asc'],
                ['server_name', 'asc'],
            ]);
    }

    public static function availablePermissions(User $user): Collection
    {
        return YnhServer::forUser($user)
            ->flatMap(function (YnhServer $server) use ($user) {
                return $server->availablePermissions($user)
                    ->map(function (string $permission) use ($server) {
                        return (object)[
                            'ynh_user_id' => null,
                            'server_id' => $server->id,
                            'server_name' => $server->name,
                            'server_ip_address' => $server->ip_address,
                            'permission' => $permission,
                        ];
                    });
            })
            ->unique(function ($item) {
                return $item->server_id . $item->permission;
            })
            ->sortBy([
                ['permission', 'asc'],
                ['server_name', 'asc'],
            ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(YnhUser::class, 'ynh_user_id', 'id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(YnhApplication::class, 'ynh_application_id', 'id');
    }
}
