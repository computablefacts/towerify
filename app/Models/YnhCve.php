<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string os
 * @property string version
 * @property string package
 * @property string cve
 * @property string status
 * @property string urgency
 * @property string fixed_version
 * @property string tracker
 */
class YnhCve extends Model
{
    use HasFactory;

    protected $table = 'ynh_cves';

    protected $fillable = [
        'os',
        'version',
        'package',
        'cve',
        'status',
        'urgency',
        'fixed_version',
        'tracker',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verify if one or more CVE affect a specific package.
     *
     * @param string $os the OS.
     * @param string $codename the OS codename.
     * @param string $package the installed app name.
     * @param string $version the installed app version.
     * @param array $criticity the urgency levels to consider (optional).
     * @return Collection the list of CVE (if any).
     */
    public static function appCves(string $os, string $codename, string $package, string $version, array $criticity = ['low', 'medium', 'high', 'unimportant', 'not yet assigned', 'end-of-life']): Collection
    {
        if ($os !== 'debian') {
            if ($os !== 'ubuntu') {
                return collect();
            }
            return YnhCve::where('os', 'debian')
                ->where('status', 'resolved')
                ->where('package', $package)
                ->whereIn('urgency', $criticity)
                ->get()
                ->filter(function ($cve) use ($version) {

                    $versionInstalledEscaped = escapeshellarg($version);
                    $versionFixedEscaped = escapeshellarg($cve->fixed_version);
                    $command = "dpkg --compare-versions {$versionInstalledEscaped} lt {$versionFixedEscaped}";

                    exec($command, $output, $returnVar);

                    return $returnVar === 0;
                });
        }
        if (!in_array($codename, ['stretch', 'buster', 'bullseye', 'sid', 'trixie'])) {
            return collect();
        }
        return YnhCve::where('os', 'debian')
            ->where('status', 'resolved')
            ->where('version', $codename)
            ->where('package', $package)
            ->whereIn('urgency', $criticity)
            ->get()
            ->filter(function ($cve) use ($version) {

                $versionInstalledEscaped = escapeshellarg($version);
                $versionFixedEscaped = escapeshellarg($cve->fixed_version);
                $command = "dpkg --compare-versions {$versionInstalledEscaped} lt {$versionFixedEscaped}";

                exec($command, $output, $returnVar);

                return $returnVar === 0;
            });
    }
}
