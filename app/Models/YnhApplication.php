<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string name
 * @property ?string description
 * @property string version
 * @property ?string path
 * @property string sku
 * @property int ynh_server_id
 * @property ?int ynh_order_id
 * @property bool updated
 */
class YnhApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'version',
        'path',
        'sku',
        'ynh_server_id',
        'updated',
        'ynh_order_id',
    ];

    protected $casts = [
        'updated' => 'boolean',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'ynh_server_id', 'id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(YnhPermission::class, 'client_id', 'ynh_application_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(YnhOrder::class, 'ynh_order_id', 'id');
    }
}
