<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property int ynh_user_id
 * @property int ynh_application_id
 * @property bool updated
 * @property bool is_visitors
 * @property bool is_all_users
 * @property bool is_user_specific
 */
class YnhPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ynh_user_id',
        'ynh_application_id',
        'updated',
        'is_visitors',
        'is_all_users',
        'is_user_specific',
    ];

    protected $casts = [
        'updated' => 'boolean',
        'is_visitors' => 'boolean',
        'is_all_users' => 'boolean',
        'is_user_specific' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function apps(User $user): Collection
    {
        $users = YnhUser::from($user)->map(fn(YnhUser $ynhUser) => $ynhUser->id)->all();
        return YnhPermission::whereIn('ynh_user_id', $users)
            ->whereNotIn('name', ['sftp.main', 'ssh.main'])
            ->where('is_user_specific', true)
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
        /** @var YnhUser $ynhUser */
        $ynhUser = YnhUser::from($user)->first();
        return YnhServer::forUser($user)
            ->flatMap(function (YnhServer $server) use ($user, $ynhUser) {
                return $server->availablePermissions($user)
                    ->map(function (string $permission) use ($server, $ynhUser) {
                        return (object)[
                            'ynh_user_id' => $ynhUser?->id,
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
