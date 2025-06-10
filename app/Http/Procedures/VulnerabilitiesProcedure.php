<?php

namespace App\Http\Procedures;

use App\Models\Alert;
use App\Models\Asset;
use App\Models\HiddenAlert;
use Illuminate\Http\Request;
use Sajya\Server\Attributes\RpcMethod;
use Sajya\Server\Procedure;

class VulnerabilitiesProcedure extends Procedure
{
    public static string $name = 'vulnerabilities';

    #[RpcMethod(
        description: "List the user's vulnerabilities.",
        params: [
            "asset_id" => "The asset id (optional).",
        ],
        result: [
            "high" => "A list of vulnerabilities with critical severity.",
            "medium" => "A list of vulnerabilities with medium severity.",
            "low" => "A list of vulnerabilities with low severity.",
        ]
    )]
    public function list(Request $request): array
    {
        if (!$request->user()->canUseAdversaryMeter()) {
            throw new \Exception('Missing permission.');
        }

        $params = $request->validate([
            'asset_id' => 'nullable|integer|exists:am_assets,id',
        ]);

        $assetId = $params['asset_id'] ?? null;
        $alerts = Asset::where('is_monitored', true)
            ->when($assetId, fn($query, $assetId) => $query->where('id', $assetId))
            ->get()
            ->flatMap(fn(Asset $asset) => $asset->alerts()->get())
            ->filter(fn(Alert $alert) => $alert->is_hidden === 0);

        return [
            'high' => $alerts->filter(fn(Alert $alert) => $alert->level === 'High')->values(),
            'medium' => $alerts->filter(fn(Alert $alert) => $alert->level === 'Medium')->values(),
            'low' => $alerts->filter(fn(Alert $alert) => $alert->level === 'Low')->values(),
        ];
    }

    #[RpcMethod(
        description: "Hide/Show one or more vulnerabilities.",
        params: [
            'uid' => 'The vulnerability unique identifier (optional).',
            'type' => 'The vulnerability type (optional).',
            'title' => 'The vulnerability title (optional).',
        ],
        result: [
            "msg" => "A success message.",
        ]
    )]
    public function toggleVisibility(Request $request): array
    {
        if (!$request->user()->canUseAdversaryMeter()) {
            throw new \Exception('Missing permission.');
        }

        $params = $request->validate([
            'uid' => 'nullable|string',
            'type' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        $uid = trim($params['uid'] ?? '');
        $type = trim($params['type'] ?? '');
        $title = trim($params['title'] ?? '');

        if (empty($uid) && empty($type) && empty($title)) {
            throw new \Exception('At least one of uid, type or title must be present.');
        }

        $query = HiddenAlert::query();

        if (!empty($uid)) {
            $query->where('uid', $uid);
        } else if (!empty($type)) {
            $query->where('type', $type);
        } else if (!empty($title)) {
            $query->where('title', $title);
        }

        /** @var HiddenAlert $marker */
        $marker = $query->first();

        if ($marker) {
            $marker->delete();
            $isVisible = true;
        } else {
            $marker = HiddenAlert::create([
                'uid' => $uid,
                'type' => $type,
                'title' => $title,
            ]);
            $isVisible = false;
        }
        return [
            "msg" => $isVisible ?
                "Your alerts will be visible from now on!" :
                "Your alerts will be hidden from now on!",
        ];
    }
}