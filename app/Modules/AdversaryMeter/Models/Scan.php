<?php

namespace App\Modules\AdversaryMeter\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int asset_id
 * @property ?string ports_scan_id
 * @property ?string vulns_scan_id
 * @property ?Carbon ports_scan_begins_at
 * @property ?Carbon ports_scan_ends_at
 * @property ?Carbon vulns_scan_begins_at
 * @property ?Carbon vulns_scan_ends_at
 */
class Scan extends Model
{
    use HasFactory;

    protected $table = 'am_scans';

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function port(): HasOne
    {
        return $this->hasOne(Port::class, 'scan_id', 'id');
    }

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

    public function markAsFailed(): void
    {
        Scan::where('asset_id', $this->asset_id)
            ->where('ports_scan_id', $this->ports_scan_id)
            ->delete();
    }
}
