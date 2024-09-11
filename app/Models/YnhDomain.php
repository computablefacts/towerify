<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string name
 * @property bool is_principal
 * @property int ynh_server_id
 * @property bool updated
 */
class YnhDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_principal',
        'ynh_server_id',
        'updated',
    ];

    protected $casts = [
        'is_principal' => 'boolean',
        'updated' => 'boolean',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'ynh_server_id', 'id');
    }
}
