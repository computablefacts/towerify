<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum;
use App\Modules\AdversaryMeter\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
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
}
