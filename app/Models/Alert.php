<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
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

    protected $table = 'am_alerts';

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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function asset(): Asset
    {
        return Asset::select('am_assets.*')
            ->join('am_scans', 'am_scans.asset_id', '=', 'am_assets.id')
            ->join('am_ports', 'am_ports.scan_id', '=', 'am_scans.id')
            ->join('am_alerts', 'am_alerts.port_id', '=', 'am_ports.id')
            ->where('am_alerts.id', $this->id)
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
            ->join('am_honeypots', 'am_honeypots.id', '=', 'am_honeypots_events.honeypot_id')
            ->where('am_honeypots_events.event', 'cve_tested')
            ->whereLike('am_honeypots_events.details', 'CVE-%')
            ->whereNotIn('am_honeypots_events.ip', $ips)
            ->whereRaw("TRIM(UPPER(am_honeypots_events.details)) = '{$cveId}'");
        if ($attackerId) {
            $events->where('am_honeypots_events.attacker_id', $attackerId);
        }
        return $events;
    }
}
