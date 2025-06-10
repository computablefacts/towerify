<?php

namespace App\Http\Controllers;

use App\Http\Procedures\AssetsProcedure;
use App\Models\Alert;
use App\Models\Asset;
use App\Models\AssetTagHash;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** @deprecated */
class CyberTodoController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show(string $hash)
    {
        $request = new Request([
            'group' => $hash,
        ]);
        return view('cyber-todo', [
            'hash' => (new AssetsProcedure())->getGroup($request)['group']
        ]);
    }

    public function markAsResolved(Alert $alert, Request $request)
    {
        $request->merge([
            'group' => $request->string('hash'),
            'vulnerability_id' => $alert->id,
        ]);
        (new AssetsProcedure())->resolveVulnerabilityInGroup($request);
        return [];
    }

    public function vulns(string $hash)
    {
        try {
            /** @var AssetTagHash $hash */
            $hash = AssetTagHash::where('hash', $hash)->first();

            if ($hash === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hash not found'
                ], 404);
            }

            $hash->views = (int)$hash->views + 1;
            $hash->update();

            $assets = Asset::select('am_assets.*')
                ->where('am_assets.is_monitored', true)
                ->join('am_assets_tags', 'am_assets_tags.asset_id', '=', 'am_assets.id')
                ->join('am_assets_tags_hashes', 'am_assets_tags_hashes.tag', '=', 'am_assets_tags.tag')
                ->where('am_assets_tags_hashes.hash', $hash->hash)
                ->get();

            return $assets->flatMap(fn(Asset $asset) => $asset->alerts()->get()->map(function (Alert $alert) use ($asset) {
                $port = $alert->port();
                return [
                    'id' => $alert->id,
                    'asset' => $asset->asset,
                    'ip' => $port->ip,
                    'port' => $port->port,
                    'level' => $alert->level,
                    'vulnerability' => $alert->vulnerability,
                    'remediation' => $alert->remediation,
                    'is_scan_in_progress' => $asset->next_scan_id != null,
                ];
            }))->toArray();

        } catch (Exception $e) {
            Log::error('Failed to get the vulnerabilities by hash : ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get the vulnerabilities by hash : ' . $e->getMessage()
            ]);
        }
    }
}