<?php

namespace App\Modules\AdversaryMeter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    use HasFactory;

    protected $table = 'scans';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'asset_id',
        'ports_scan_id',
        'vulns_scan_id',
        'ports_scan_begins_at',
        'ports_scan_ends_at',
        'vulns_scan_begins_at',
        'vulns_scan_ends_at',
    ];

    protected $casts = [
        'ports_scan_begins_at' => 'datetime',
        'ports_scan_ends_at' => 'datetime',
        'vulns_scan_begins_at' => 'datetime',
        'vulns_scan_ends_at' => 'datetime',
    ];

    public function portsScanIsRunning(): bool
    {
        return $this->ports_scan_id && $this->ports_scan_begins_at && !$this->ports_scan_ends_at;
    }

    public function portsScanHasEnded(): bool
    {
        return $this->ports_scan_id && $this->ports_scan_begins_at && $this->ports_scan_ends_at;
    }

    public function vulnsScanIsRunning(): bool
    {
        return $this->vulns_scan_id && $this->vulns_scan_begins_at && !$this->vulns_scan_ends_at;
    }

    public function vulnsScanHasEnded(): bool
    {
        return $this->vulns_scan_id && $this->vulns_scan_begins_at && $this->vulns_scan_ends_at;
    }

    public function markAssetScanAsFailed(): void
    {
        Scan::where('asset_id', $this->asset_id)
            ->where('ports_scan_id', $this->ports_scan_id)
            ->delete();
    }
}
