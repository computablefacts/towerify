<?php

namespace App\Jobs;

use App\Models\YnhOsquery;
use App\Models\YnhServer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadDebianSecurityBugTracker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const string SECURITY_BUG_TRACKER = 'https://security-tracker.debian.org/tracker/data/json';

    public $tries = 1;
    public $maxExceptions = 1;
    public $timeout = 3 * 180; // 9mn

    public function __construct()
    {
        //
    }

    public static function cves(YnhServer $server, string $app, string $versionInstalled): array
    {
        $file = "/tmp/deb-cve-{$app}.json";

        if (file_exists($file)) {

            $osInfo = YnhOsquery::osInfos(collect([$server]))->first();

            if ($osInfo && $osInfo->os === 'debian') {

                $codename = $osInfo->codename;

                if (in_array($codename, ['stretch', 'buster', 'bullseye', 'sid', 'trixie'])) {

                    // Get the list of CVE for the given app
                    $cves = [];
                    $json = json_decode(file_get_contents($file), true);

                    foreach ($json as $cve => $obj) {
                        if (isset($obj['releases'][$codename]['status']) && $obj['releases'][$codename]['status'] === 'resolved') {

                            // Filter out CVE already patched in the installed version
                            $versionFixed = $obj['releases'][$codename]['fixed_version'];
                            $versionInstalledEscaped = escapeshellarg($versionInstalled);
                            $versionFixedEscaped = escapeshellarg($versionFixed);
                            $command = "dpkg --compare-versions {$versionInstalledEscaped} lt {$versionFixedEscaped}";

                            exec($command, $output, $returnVar);

                            if ($returnVar === 0) {
                                $cves[] = [
                                    'cve' => $cve,
                                    'status' => 'resolved',
                                    'urgency' => $obj['releases'][$codename]['urgency'],
                                    'fixed_version' => $versionFixed,
                                    'tracker' => "https://security-tracker.debian.org/tracker/{$cve}"
                                ];
                            }
                        }
                    }
                    return $cves;
                }
            }
        }
        return [];
    }

    public function handle()
    {
        // Cleanup
        shell_exec("rm -rf /tmp/deb-cve-*.json");

        // Download the feed and create one json for each app
        $url = self::SECURITY_BUG_TRACKER;
        shell_exec("wget -qO- {$url} | jq -r 'to_entries[] | \"\(.key)\\t\(.value)\"' | awk -F'\\t' '{print $2 >\"/tmp/deb-cve-\"$1\".json\"}'");
    }
}
