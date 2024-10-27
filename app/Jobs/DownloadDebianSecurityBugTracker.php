<?php

namespace App\Jobs;

use App\Models\YnhCve;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function handle()
    {
        // Cleanup
        shell_exec("rm -rf /tmp/deb-cve-*.json");

        // Download the feed and create one json for each app
        $url = self::SECURITY_BUG_TRACKER;
        shell_exec("wget -qO- {$url} | jq -r 'to_entries[] | \"\(.key)\\t\(.value)\"' | awk -F'\\t' '{print $2 >\"/tmp/deb-cve-\"$1\".json\"}'");

        // Update the database of CVE
        DB::transaction(function () {

            YnhCve::query()->truncate();

            $files = glob('/tmp/deb-cve-*.json');

            foreach ($files as $file) {

                $package = Str::after($file, '/tmp/deb-cve-');
                $package = Str::before($package, '.json');
                $json = json_decode(file_get_contents($file), true);

                foreach ($json as $cve => $obj) {
                    foreach ($obj['releases'] as $release => $obj2) {
                        if ($obj2['status'] === 'resolved') {
                            YnhCve::create([
                                'os' => 'debian',
                                'version' => $release,
                                'package' => $package,
                                'cve' => $cve,
                                'status' => $obj2['status'],
                                'urgency' => $obj2['urgency'],
                                'fixed_version' => $obj2['fixed_version'],
                                'tracker' => "https://security-tracker.debian.org/tracker/{$cve}",
                            ]);
                        }
                    }
                }
            }
        });
    }
}
