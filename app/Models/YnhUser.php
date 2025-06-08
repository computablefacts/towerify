<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string username
 * @property ?string fullname
 * @property string email
 * @property int ynh_server_id
 * @property bool updated
 */
class YnhUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'fullname',
        'email',
        'ynh_server_id',
        'updated',
    ];

    protected $casts = [
        'updated' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function from(User $user): Collection
    {
        $tenantId = $user->tenant_id;

        if ($tenantId) {

            $customerId = $user->customer_id;

            if ($customerId) {
                return YnhUser::select('ynh_users.*')
                    ->join('ynh_servers', 'ynh_servers.id', '=', 'ynh_users.ynh_server_id')
                    ->join('users', 'users.id', '=', 'ynh_servers.user_id')
                    ->where('ynh_users.username', $user->ynhUsername())
                    ->where('users.tenant_id', $tenantId)
                    ->where('users.customer_id', $customerId)
                    ->get();
            }
            return YnhUser::select('ynh_users.*')
                ->join('ynh_servers', 'ynh_servers.id', '=', 'ynh_users.ynh_server_id')
                ->join('users', 'users.id', '=', 'ynh_servers.user_id')
                ->where('ynh_users.username', $user->ynhUsername())
                ->where('users.tenant_id', $tenantId)
                ->get();
        }
        return YnhUser::where('username', $user->ynhUsername())->get();
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(YnhPermission::class);
    }
}
