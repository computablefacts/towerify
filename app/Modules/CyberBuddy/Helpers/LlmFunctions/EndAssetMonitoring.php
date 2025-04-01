<?php

namespace App\Modules\CyberBuddy\Helpers\LlmFunctions;

use App\Modules\AdversaryMeter\Http\Controllers\AssetController;
use App\Modules\AdversaryMeter\Models\Asset;
use App\User;

class EndAssetMonitoring extends AbstractLlmFunction
{
    public function html(): string
    {
        return $this->output;
    }

    public function text(): string
    {
        return $this->output;
    }

    protected function schema2(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "end_asset_monitoring",
                "description" => "Stop the monitoring of an existing asset.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "asset" => [
                            "type" => ["string"],
                            "description" => "The asset's IP address, domain or subdomain.",
                        ],
                    ],
                    "required" => [
                        "asset"
                    ],
                    "additionalProperties" => false,
                ],
                "strict" => true,
            ],
        ];
    }

    protected function handle2(User $user, string $threadId, array $args): AbstractLlmFunction
    {
        try {
            /** @var Asset $asset */
            $asset = Asset::where('asset', $args['asset'] ?? null)->firstOrFail();

            $controller = new AssetController();
            $response = $controller->assetMonitoringEnds($asset);

            $this->output = "The monitoring of {$asset->asset} ended.";

        } catch (\Exception $e) {
            $this->output = $e->getMessage();
        }
        return $this;
    }
}
