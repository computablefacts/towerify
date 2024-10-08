<?php

namespace App\Models;

use App\Enums\OsqueryPlatformEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property string description
 * @property ?string value
 * @property string query
 * @property ?string version
 * @property int interval
 * @property bool removed
 * @property bool snapshot
 * @property OsqueryPlatformEnum platform
 * @property ?string category
 */
class YnhOsqueryRule extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_rules';

    protected $fillable = [
        'name',
        'description',
        'value',
        'version',
        'query',
        'interval',
        'removed',
        'snapshot',
        'platform',
        'category',
    ];

    protected $casts = [
        'removed' => 'boolean',
        'snapshot' => 'boolean',
        'platform' => OsqueryPlatformEnum::class,
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'ynh_osquery_rules_scope_tenant', 'rule_id', 'tenant_id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'ynh_osquery_rules_scope_customer', 'rule_id', 'customer_id');
    }
}
