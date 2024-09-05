<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'port_id',
        'type',
        'vulnerability',
        'remediation',
        'level',
        'uid',
        'cve_id',
        'cve_cvss',
        'cve_vendor',
        'cve_product',
        'title',
        'flarum_slug',
    ];

    public function port(): ?Port
    {
        return Port::find($this->port_id)->first();
    }
}
