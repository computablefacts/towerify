<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum;
use App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Honeypot extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'honeypots';
    protected $connection = 'mysql_am';

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

    public function events(): HasMany
    {
        return $this->hasMany(HoneypotEvent::class, 'honeypot_id', 'id');
    }
}
