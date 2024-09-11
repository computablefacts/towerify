<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int id
 * @property int port_id
 * @property ?string uid
 * @property string type
 * @property ?string level
 * @property ?string title
 * @property ?string vulnerability
 * @property ?string remediation
 * @property ?string cve_id
 * @property ?string cve_cvss
 * @property ?string cve_vendor
 * @property ?string cve_product
 */
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

    public function asset(): Asset
    {
        return Asset::select('assets.*')
            ->join('scans', 'scans.asset_id', '=', 'assets.id')
            ->join('ports', 'ports.scan_id', '=', 'scans.id')
            ->join('alerts', 'alerts.port_id', '=', 'ports.id')
            ->where('alerts.id', $this->id)
            ->first();
    }

    public function port(): Port
    {
        return Port::find($this->port_id);
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
