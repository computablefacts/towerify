<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int ynh_server_id
 * @property int ynh_cve_id
 * @property string os
 * @property string os_version
 * @property string package
 * @property string package_version
 */
class YnhOsqueryPackage extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_packages';

    protected $fillable = [
        'ynh_server_id',
        'ynh_cve_id',
        'os',
        'os_version',
        'package',
        'package_version',
        'cves',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cves' => 'array',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }

    public function cve(): BelongsTo
    {
        return $this->belongsTo(YnhCve::class);
    }
}
