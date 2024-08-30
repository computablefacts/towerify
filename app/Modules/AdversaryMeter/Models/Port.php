<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Port extends Model
{
    use HasFactory;

    protected $table = 'ports';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'scan_id',
        'hostname',
        'ip',
        'port',
        'protocol',
        'country',
        'hosting_service_description',
        'hosting_service_registry',
        'hosting_service_asn',
        'hosting_service_cidr',
        'hosting_service_country_code',
        'hosting_service_date',
        'service',
        'product',
        'ssl',
    ];

    protected $casts = [
        'ssl' => 'boolean',
    ];

    public function tags(): HasMany
    {
        return $this->hasMany(PortTag::class, 'port_id', 'id');
    }
}
