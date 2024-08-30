<?php

namespace App\Modules\AdversaryMeter\Models;

use App\Modules\AdversaryMeter\Enums\AssetTypesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';
    protected $connection = 'mysql_am';

    protected $fillable = [
        'asset',
        'asset_type',
        'tld',
        'prev_scan_id',
        'cur_scan_id',
        'next_scan_id',
        'discovery_id',
    ];

    protected $casts = [
        'asset_type' => AssetTypesEnum::class
    ];

    public function isDns(): bool
    {
        return $this->asset_type === AssetTypesEnum::DNS;
    }

    public function isIp(): bool
    {
        return $this->asset_type === AssetTypesEnum::IP;
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

    public function isDiscoveryRunning(): bool
    {
        return $this->discovery_id !== null;
    }

    public function tags(): HasMany
    {
        return $this->hasMany(AssetTag::class, 'asset_id', 'id');
    }

    public function curScan(): HasMany
    {
        return $this->hasMany(Scan::class, 'id', 'cur_scan_id');
    }

    public function nextScan(): HasMany
    {
        return $this->hasMany(Scan::class, 'id', 'next_scan_id');
    }
}
