<?php

namespace App\Modules\AdversaryMeter\Http\Controllers;

use App\Modules\AdversaryMeter\Helpers\ApiUtils;
use App\Modules\AdversaryMeter\Listeners\CreateAssetListener;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTag;
use App\Modules\AdversaryMeter\Models\Port;
use App\Modules\AdversaryMeter\Models\PortTag;
use App\Modules\AdversaryMeter\Models\Scan;
use App\Modules\AdversaryMeter\Rules\IsValidAsset;
use App\Modules\AdversaryMeter\Rules\IsValidDomain;
use App\Modules\AdversaryMeter\Rules\IsValidIpAddress;
use App\Modules\AdversaryMeter\Rules\IsValidTag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InventoryController extends Controller
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
        $obj = CreateAssetListener::execute($asset, $user->id, $user->customer_id, $user->tenant_id);

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

        /** @var User $user */
        $user = Auth::user();
        $query = Asset::where('is_monitored', $valid === 'true');

        if ($user->user_id && !$user->customer_id && !$user->tenant_id) {
            $query->where('user_id', $user->user_id);
        } elseif ($user->customer_id && !$user->tenant_id) {
            $query->where('customer_id', $user->customer_id);
        } elseif ($user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }
        if ($hours) {
            $cutOffTime = now()->subHours($hours);
            $query->where('created_at', '>=', $cutOffTime);
        }

        $assets = $query->orderBy('asset')
            ->get()
            ->map(function (Asset $asset) {
                $tags = $asset->scanCompleted()
                    ->get()
                    ->flatMap(function (Scan $scan) use ($asset) {
                        return $scan->ports()
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
                            });
                    })
                    ->values()
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

    public function assetMonitoringBegins(int $id): array
    {
        $asset = Asset::find($id);

        if (!$asset) {
            abort(500, "Asset not found : {$id}");
        }
        if ($asset->is_monitored) {
            abort(500, "Asset is already monitored : {$asset->asset}");
        }

        $asset->is_monitored = true;
        $asset->save();

        return [
            'asset' => $this->convertAsset($asset),
        ];
    }

    public function assetMonitoringEnds(int $id): array
    {
        $asset = Asset::find($id);

        if (!$asset) {
            abort(500, "Asset not found : {$id}");
        }
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
            'type' => $asset->asset_type->name,
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

        $obj = $asset->tags()->create(['tag' => $tag]);

        if (!$obj) {
            abort(500, "The tag could not be created : {$tag}");
        }
        return collect([[
            'id' => $obj->id,
            'key' => $obj->tag,
        ]]);
    }

    public function removeTag(Asset $asset, AssetTag $assetTag): void
    {
        $assetTag->delete();
    }
}