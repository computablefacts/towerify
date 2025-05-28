<?php

namespace App\Http\Controllers;

use App\Helpers\VulnerabilityScannerApiUtilsFacade as ApiUtils;
use App\Http\Procedures\AssetsProcedure;
use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Screenshot;
use App\Rules\IsValidIpAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** @deprecated */
class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function discover(Request $request): array
    {
        return (new AssetsProcedure())->discover($request);
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
        return (new AssetsProcedure())->create($request);
    }

    public function userAssets(Request $request): array
    {
        $request->replace([
            'is_monitored' => $request->string('valid'),
            'created_the_last_x_hours' => $request->integer('hours'),
        ]);
        return (new AssetsProcedure())->list($request);
    }

    public function assetMonitoringBegins(Asset $asset): array
    {
        $request = new Request();
        $request->replace([
            'asset_id' => $asset->id,
        ]);
        return (new AssetsProcedure())->monitor($request);
    }

    public function assetMonitoringEnds(Asset $asset): array
    {
        $request = new Request();
        $request->replace([
            'asset_id' => $asset->id,
        ]);
        return (new AssetsProcedure())->unmonitor($request);
    }

    public function screenshot(Screenshot $screenshot): array
    {
        return [
            "screenshot" => $screenshot->png,
        ];
    }

    public function addTag(Asset $asset, Request $request): Collection
    {
        $request->replace([
            'asset_id' => $asset->id,
            'tag' => $request->string('key', ''),
        ]);
        $tag = (new AssetsProcedure())->tag($request);
        return collect([[
            'id' => $tag['tag']->id,
            'key' => $tag['tag']->tag,
        ]]);
    }

    public function removeTag(Asset $asset, AssetTag $assetTag): void
    {
        $request = new Request();
        $request->replace([
            'asset_id' => $asset->id,
            'tag_id' => $assetTag->id,
        ]);
        (new AssetsProcedure())->untag($request);
    }

    public function infosFromAsset(string $assetBase64, int $trialId = 0): array
    {
        $request = new Request([
            'asset' => base64_decode($assetBase64),
            'trial_id' => $trialId,
        ]);
        return (new AssetsProcedure())->get($request);
    }

    public function deleteAsset(Asset $asset): void
    {
        $request = new Request();
        $request->replace([
            'asset_id' => $asset->id,
        ]);
        (new AssetsProcedure())->delete($request);
    }

    public function restartScan(Asset $asset): array
    {
        $request = new Request();
        $request->replace([
            'asset_id' => $asset->id,
        ]);
        return (new AssetsProcedure())->restartScan($request);
    }
}