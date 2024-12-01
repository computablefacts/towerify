<?php

namespace App\Models;

use App\Enums\OsqueryPlatformEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string description
 * @property string query
 * @property ?string version
 * @property int interval
 * @property bool removed
 * @property bool snapshot
 * @property OsqueryPlatformEnum platform
 * @property ?string category
 * @property bool enabled
 * @property ?string attck
 * @property bool is_ioc
 * @property double score
 */
class YnhOsqueryRule extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery_rules';

    protected $fillable = [
        'name',
        'description',
        'version',
        'query',
        'interval',
        'removed',
        'snapshot',
        'platform',
        'category',
        'enabled',
        'attck',
        'is_ioc',
        'score',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'removed' => 'boolean',
        'snapshot' => 'boolean',
        'is_ioc' => 'boolean',
        'score' => 'float',
        'platform' => OsqueryPlatformEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'ynh_osquery_rules_scope_tenant', 'rule_id', 'tenant_id');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'ynh_osquery_rules_scope_customer', 'rule_id', 'customer_id');
    }

    public function mitreAttckTactics(): array
    {
        return $this->mitreAttck()->flatMap(fn(YnhMitreAttck $attck) => $attck->tactics)->unique()->sort()->toArray();
    }

    public function mitreAttck(): Collection
    {
        if ($this->attck) {
            $refs = explode(',', $this->attck);
            return YnhMitreAttck::whereIn('uid', $refs)->get();
        }
        return collect();
    }
}
