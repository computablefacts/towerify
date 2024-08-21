<?php

namespace App\Models;

use App\Enums\OsqueryPlatformEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
