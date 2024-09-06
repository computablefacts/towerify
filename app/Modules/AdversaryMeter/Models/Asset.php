<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Asset extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'assets';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'asset',
        'type',
        'tld',
        'prev_scan_id',
        'cur_scan_id',
        'next_scan_id',
        'discovery_id',
        'is_monitored',
        'created_by',
    ];

    protected $casts = [
        'type' => AssetTypesEnum::class,
        'is_monitored' => 'boolean',
    ];

    public function isDns(): bool
    {
        return $this->type === AssetTypesEnum::DNS;
    }

    public function isIp(): bool
    {
        return $this->type === AssetTypesEnum::IP;
    }

    public function isRange(): bool
    {
        return $this->type === AssetTypesEnum::RANGE;
    }

    public function tld(): ?string
    {
        if ($this->isDns()) {
            if ($this->tld) {
                return $this->tld;
            }

            // Url need to start with protocol
            if (substr($this->asset, 0, 7) === "http://" || substr($this->asset, 0, 8) === "https://") {
                $UrlElements = explode(".", parse_url($this->asset, PHP_URL_HOST));
            } else {
                $UrlElements = explode(".", parse_url('http://' . $this->asset, PHP_URL_HOST));
            }

            $lastElement = end($UrlElements);
            $prevElement = prev($UrlElements);

            if (count($UrlElements) >= 2) {
                $this->tld = mb_strtolower($prevElement . '.' . $lastElement);
                $this->save();
                return $this->tld;
            }
        }
        return null;
    }

    public function tags(): HasMany
    {
        return $this->hasMany(AssetTag::class, 'asset_id', 'id');
    }

    public function ports(): Builder
    {
        return Port::select('ports.*')
            ->join('scans', 'scans.id', '=', 'ports.scan_id')
            ->join('assets', 'assets.cur_scan_id', '=', 'scans.ports_scan_id')
            ->where('assets.id', $this->id);
    }

    public function alerts(): Builder
    {
        $hiddenUids = HiddenAlert::whereNotNull('uid')
            ->where('uid', '<>', '')
            ->get()
            ->map(fn(HiddenAlert $marker) => $marker->uid);
        $hiddenTypes = HiddenAlert::whereNotNull('type')->where('type', '<>', '')
            ->get()
            ->map(fn(HiddenAlert $marker) => addslashes($marker->type));
        $hiddenTitles = HiddenAlert::whereNotNull('title')->where('title', '<>', '')
            ->get()
            ->map(fn(HiddenAlert $marker) => addslashes($marker->title));

        $ifUids = $hiddenUids->isEmpty() ? 'false' : "alerts.uid IN ('{$hiddenUids->join("','")}')";
        $ifTypes = $hiddenTypes->isEmpty() ? 'false' : "alerts.type IN ('{$hiddenTypes->join("','")}')";
        $ifTitles = $hiddenTitles->isEmpty() ? 'false' : "alerts.title IN ('{$hiddenTitles->join("','")}')";
        $case = "CASE WHEN {$ifUids} OR {$ifTypes} OR {$ifTitles} THEN true ELSE false END AS is_hidden";

        return Alert::select('alerts.*', DB::raw($case))
            ->join('ports', 'ports.id', '=', 'alerts.port_id')
            ->join('scans', 'scans.id', '=', 'ports.scan_id')
            ->join('assets', 'assets.cur_scan_id', '=', 'scans.ports_scan_id')
            ->where('assets.id', $this->id);
    }

    public function scanCompleted(): ?Scan
    {
        return Scan::where('asset_id', $this->id)
            ->where('ports_scan_id', $this->cur_scan_id)
            ->first();
    }

    public function scanInProgress(): ?Scan
    {
        return Scan::where('asset_id', $this->id)
            ->where('ports_scan_id', $this->next_scan_id)
            ->first();
    }
}
