<?php

namespace App\Modules\AdversaryMeter\Http\Controllers;

use App\Modules\AdversaryMeter\Events\BeginPortsScan;
use App\Modules\AdversaryMeter\Helpers\ApiUtils;
use App\Modules\AdversaryMeter\Listeners\CreateAssetListener;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\PortTag;
use App\Modules\AdversaryMeter\Rules\IsValidAsset;
use App\Modules\AdversaryMeter\Rules\IsValidDomain;
use App\Modules\AdversaryMeter\Rules\IsValidIpAddress;
use App\Modules\AdversaryMeter\Rules\IsValidTag;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    private const BLACKLIST = [
        "amazonaws.com",
        "microsoft.com",
        "azure.net",
        "wordpress.com",
        "google.com",
        "co.uk",
        "co.jp",
        "com.au"
    ];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function discover(Request $request): array
    {
        $domain = trim($request->string('domain', ''));

        if (!IsValidDomain::test($domain)) {
            return [];
        }
        if (!in_array($domain, self::BLACKLIST)) {
            abort(500, "The domain is blacklisted : {$domain}");
        }
        return ApiUtils::discover_public($domain);
    }

    public function discoverFromIp(Request $request): array
    {
        $ip = trim($request->string('ip', ''));

        if (!IsValidIpAddress::test($ip)) {
            abort(500, "Invalid IP address : {$ip}");
        }
        return ApiUtils::discover_from_ip_public($ip);
    }

    public function saveAsset(Request $request): array
    {
        $asset = $request->string('asset');
        $watch = $request->boolean('watch');

        if (!IsValidAsset::test($asset)) {
            abort(500, "Invalid asset : {$asset}");
        }

        /** @var User $user */
        $user = Auth::user();
        $obj = CreateAssetListener::execute($asset);

        if (!$obj) {
            abort(500, "The asset could not be created : {$asset}");
        }
        if (is_bool($watch) && $watch) {
            $obj->is_monitored = true;
            $obj->save();
        }

        $obj = $obj->refresh();

        return [
            'asset' => $this->convertAsset($obj),
        ];
    }

    public function userAssets(Request $request): array
    {
        $valid = Str::lower($request->string('valid'));
        $hours = $request->integer('hours');

        $query = Asset::where('is_monitored', $valid === 'true');

        if ($hours) {
            $cutOffTime = now()->subHours($hours);
            $query->where('created_at', '>=', $cutOffTime);
        }

        $assets = $query->orderBy('asset')
            ->get()
            ->map(function (Asset $asset) {
                $tags = $asset->ports()
                    ->orderBy('port')
                    ->get()
                    ->flatMap(function (Port $port) use ($asset) {
                        return $port->tags()
                            ->orderBy('tag')
                            ->get()
                            ->map(function (PortTag $tag) use ($port, $asset) {
                                if ($asset->isRange()) {
                                    return [
                                        'asset' => $port->ip,
                                        'port' => $port->port,
                                        'tag' => Str::lower($tag->tag),
                                        'is_range' => true,
                                    ];
                                }
                                return [
                                    'asset' => $asset->asset,
                                    'port' => $port->port,
                                    'tag' => Str::lower($tag->tag),
                                    'is_range' => false,
                                ];
                            });
                    })
                    ->toArray();
                return array_merge($this->convertAsset($asset), [
                    'tags_from_ports' => $tags
                ]);
            })
            ->all();

        return [
            'assets' => $assets,
        ];
    }

    public function assetMonitoringBegins(Asset $asset): array
    {
        if ($asset->is_monitored) {
            abort(500, "Asset is already monitored : {$asset->asset}");
        }

        $asset->is_monitored = true;
        $asset->save();

        return [
            'asset' => $this->convertAsset($asset),
        ];
    }

    public function assetMonitoringEnds(Asset $asset): array
    {
        if (!$asset->is_monitored) {
            abort(500, "Asset is not monitored : {$asset->asset}");
        }

        $asset->is_monitored = false;
        $asset->save();

        return [
            'asset' => $this->convertAsset($asset),
        ];
    }

    private function convertAsset(Asset $asset): array
    {
        return [
            'uid' => $asset->id,
            'asset' => $asset->asset,
            'tld' => $asset->tld(),
            'type' => $asset->type->name,
            'status' => $asset->is_monitored ? 'valid' : 'invalid',
            'tags' => $asset->tags()
                ->get()
                ->map(fn(AssetTag $tag) => [
                    'id' => $tag->id,
                    'name' => $tag->tag,
                ])
                ->all(),
        ];
    }

    public function screenshot(int $id): array
    {
        // TODO : backport code
        return [
            "screenshot" => null,
        ];
    }

    public function addTag(Asset $asset, Request $request): Collection
    {
        $tag = Str::lower($request->string('key', ''));

        if (!IsValidTag::test($tag)) {
            abort(500, "Invalid tag : {$tag}");
        }

        $obj = $asset->tags()->where('tag', $tag)->first();

        if (!$obj) {
            $obj = $asset->tags()->create(['tag' => $tag]);
            if (!$obj) {
                abort(500, "The tag could not be created : {$tag}");
            }
        }
        return collect([[
            'id' => $obj->id,
            'key' => $obj->tag,
        ]]);
    }

    public function removeTag(Asset $asset, AssetTag $assetTag): void
    {
        if ($asset->id === $assetTag->asset_id) {
            $assetTag->delete();
        }
    }

    public function infosFromAsset(string $assetBase64): array
    {
        $domainOrIpOrRange = base64_decode($assetBase64);
        $asset = Asset::where('asset', $domainOrIpOrRange)->first();

        if (!$asset) {

            // The asset cannot be identified: check if it is an IP address from a known range
            if (IsValidIpAddress::test($domainOrIpOrRange)) {
                $asset = Asset::select('assets.*')
                    ->join('scans', 'scans.ports_scan_id', '=', 'assets.cur_scan_id')
                    ->join('ports', 'ports.scan_id', '=', 'scans.id')
                    ->where('ports.ip', $domainOrIpOrRange)
                    ->first();
                if ($asset) {
                    return $this->infosFromAsset(base64_encode($asset->asset));
                }
            }
            return [];
        }

        // Load the asset's tags
        $tags = $asset->tags()->get()->map(fn(AssetTag $tag) => $tag->tag)->toArray();

        // Load the asset's open ports
        $ports = $asset->ports()
            ->get()
            ->map(function (Port $port) {
                return [
                    'ip' => $port->ip,
                    'port' => $port->port,
                    'protocol' => $port->protocol,
                    'products' => [$port->product],
                    'services' => [$port->service],
                    'tags' => $port->tags()->get()->map(fn(PortTag $tag) => $tag->tag)->toArray(),
                    'screenshotId' => null,
                ];
            })
            ->toArray();

        // Load the asset's alerts
        $alerts = $asset->alerts()
            ->get()
            ->map(function (Alert $alert) use ($asset) {

                $port = $alert->port();

                return [
                    'id' => $alert->id,
                    'ip' => $port->ip,
                    'port' => $port->port,
                    'protocol' => $port->protocol,
                    'type' => $alert->type,
                    'tested' => $alert->events()->exists(),
                    'vulnerability' => $alert->vulnerability,
                    'remediation' => $alert->remediation,
                    'level' => Str::lower($alert->level),
                    'uid' => $alert->uid,
                    'cve_id' => $alert->cve_id,
                    'cve_cvss' => $alert->cve_cvss,
                    'cve_vendor' => $alert->cve_vendor,
                    'cve_product' => $alert->cve_product,
                    'title' => $alert->title,
                    'flarum_url' => null,
                    'start_date' => $alert->created_at,
                ];
            });

        // Load the asset's scans
        $scanInProgress = $asset->scanInProgress();

        if ($scanInProgress) {
            $scan = $scanInProgress;
        } else {
            $scan = $asset->scanCompleted();
        }

        $frequency = config('towerify.adversarymeter.days_between_scans');
        $nextScanDate = $asset->is_monitored ? $scan ? Carbon::parse($scan->ports_scan_begins_at)->addDays((int)$frequency) : Carbon::now() : null;

        // Load the identity of the user who created the asset
        $user = null; // TODO : User::find($asset->user_id)->first();

        return [
            'asset' => $asset->asset,
            'modifications' => [[
                'asset_id' => $asset->id,
                'asset_name' => $asset->asset,
                'timestamp' => $asset->updated_at,
                'user' => $user ? $user->email : 'unknown',
            ]],
            'tags' => $tags,
            'ports' => $ports,
            'vulnerabilities' => $alerts->filter(fn(Alert $alert) => !$alert->is_hidden)->toArray(),
            'timeline' => [
                'nmap' => [
                    'id' => optional($scan)->ports_scan_id,
                    'start' => optional($scan)->ports_scan_begins_at,
                    'end' => optional($scan)->ports_scan_ends_at,
                ],
                'sentinel' => [
                    'id' => optional($scan)->vulns_scan_id,
                    'start' => optional($scan)->vulns_scan_begins_at,
                    'end' => optional($scan)->vulns_scan_ends_at,
                ],
                'next_scan' => $nextScanDate,
            ],
            'hiddenAlerts' => $alerts->filter(fn(Alert $alert) => $alert->is_hidden)->toArray(),
        ];
    }

    public function deleteAsset(Asset $asset): void
    {
        if ($asset->is_monitored) {
            abort(500, 'Deletion not allowed, asset is monitored.');
        }
        $asset->delete();
    }

    public function restartScan(Asset $asset): array
    {
        if (!$asset->is_monitored) {
            abort(500, 'Restart scan not allowed, asset is not monitored.');
        }
        if (!$asset->scanInProgress()) {
            event(new BeginPortsScan($asset));
        }
        return [
            'asset' => $this->convertAsset($asset),
        ];
    }
}