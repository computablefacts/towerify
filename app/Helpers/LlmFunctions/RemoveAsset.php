<?php

namespace App\Helpers\LlmFunctions;

use App\Modules\AdversaryMeter\Http\Controllers\AssetController;
use App\Modules\AdversaryMeter\Models\Asset;
use App\User;

class RemoveAsset extends AbstractLlmFunction
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
                "name" => "remove_asset",
                "description" => "Remove an existing asset.",
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
            $controller->deleteAsset($asset);

            $this->output = "The {$asset->asset} has been removed.";

        } catch (\Exception $e) {
            $this->output = $e->getMessage();
        }
        return $this;
    }
}
