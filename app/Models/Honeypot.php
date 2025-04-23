<?php

namespace App\Models;

use App\Enums\HoneypotCloudProvidersEnum;
use App\Enums\HoneypotCloudSensorsEnum;
use App\Enums\HoneypotStatusesEnum;
use App\Traits\HasTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string dns
 * @property ?HoneypotStatusesEnum status
 * @property HoneypotCloudProvidersEnum cloud_provider
 * @property HoneypotCloudSensorsEnum cloud_sensor
 */
class Honeypot extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'am_honeypots';

    protected $fillable = [
        'dns',
        'status',
        'cloud_provider',
        'cloud_sensor',
        'created_by',
    ];

    protected $casts = [
        'status' => HoneypotStatusesEnum::class,
        'cloud_provider' => HoneypotCloudProvidersEnum::class,
        'cloud_sensor' => HoneypotCloudSensorsEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function id(): int
    {
        return $this->id;
    }

    public function dns(): string
    {
        return $this->dns;
    }

    public function status(): ?HoneypotStatusesEnum
    {
        return $this->status;
    }

    public function cloudProvider(): HoneypotCloudProvidersEnum
    {
        return $this->cloud_provider;
    }

    public function cloudSensor(): HoneypotCloudSensorsEnum
    {
        return $this->cloud_sensor;
    }

    public function events(): HasMany
    {
        return $this->hasMany(HoneypotEvent::class, 'honeypot_id', 'id');
    }

    public function attackers(): Builder
    {
        return Attacker::select('am_attackers.*')
            ->join('am_honeypots_events', 'am_honeypots_events.attacker_id', '=', 'am_attackers.id')
            ->where('am_honeypots_events.honeypot_id', $this->id);
    }
}
