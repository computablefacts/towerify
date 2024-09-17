<?php

namespace App\Modules\AdversaryMeter\Http\Controllers;

use App\Modules\AdversaryMeter\Events\BeginPortsScan;
use App\Modules\AdversaryMeter\Models\Alert;
use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\AssetTagHash;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CyberTodoController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show(string $hash)
    {
        $hash = AssetTagHash::where('hash', $hash)->first();
        if (!$hash) {
            abort(404, 'Hash not found');
        }
        return view('cyber-todo', ['hash' => $hash]);
    }

    public function markAsResolved(Alert $alert, Request $request)
    {
        $hash = $request->validate([
            'hash' => 'required|string',
        ])['hash'];

        $hash = AssetTagHash::where('hash', $hash)->first();

        if ($hash === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hash not found'
            ], 404);
        }

        $asset = $alert->asset();

        if (!$asset) {
            abort(500, "Unknown asset : {$asset}");
        }
        if (!$asset->is_monitored) {
            abort(500, 'Restart scan not allowed, asset is not monitored.');
        }
        if ($asset->scanInProgress()->isEmpty()) {
            event(new BeginPortsScan($asset));
        }
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

            $assets = Asset::select('assets.*')
                ->where('is_monitored', true)
                ->join('assets_tags', 'assets_tags.asset_id', '=', 'assets.id')
                ->join('assets_tags_hashes', 'assets_tags_hashes.tag', '=', 'assets_tags.tag')
                ->where('assets_tags_hashes.hash', $hash->hash)
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