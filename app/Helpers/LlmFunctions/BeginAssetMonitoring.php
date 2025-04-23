<?php

namespace App\Helpers\LlmFunctions;

use App\Modules\AdversaryMeter\Http\Controllers\AssetController;
use App\User;
use Illuminate\Http\Request;

class BeginAssetMonitoring extends AbstractLlmFunction
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
                "name" => "begin_asset_monitoring",
                "description" => "Monitor a new or an existing asset.",
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
            $asset = $args['asset'] ?? null;

            $request = new Request();
            $request->replace([
                'asset' => $asset,
                'watch' => true,
            ]);

            $controller = new AssetController();
            $response = $controller->saveAsset($request);

            $this->output = "Congratulation! The monitoring of $asset started.";

        } catch (\Exception $e) {
            $this->output = $e->getMessage();
        }
        return $this;
    }
}
