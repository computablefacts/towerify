<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function events(?int $attackerId = null): Builder
    {
        /** @var array $ips */
        $ips = config('towerify.adversarymeter.ip_addresses');
        $cveId = trim(Str::upper($this->cve_id));
        $events = HoneypotEvent::query()
            ->join('honeypots', 'honeypots.id', '=', 'honeypots_events.honeypot_id')
            ->where('honeypots_events.event', 'cve_tested')
            ->whereLike('honeypots_events.event', 'CVE-%')
            ->whereNotIn('honeypots_events.ip', $ips)
            ->whereRaw("TRIM(UPPER(honeypots_events.details)) = '{$cveId}'");
        if ($attackerId) {
            $events->where('honeypots_events.attacker_id', $attackerId);
        }
        return $events;
    }
}
