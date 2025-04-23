<?php

namespace App\Helpers\LlmFunctions;

use App\Modules\AdversaryMeter\Http\Controllers\AssetController;
use App\User;
use Illuminate\Http\Request;

class DiscoverAssets extends AbstractLlmFunction
{
    public function html(): string
    {
        $header = "<th>Asset</th>";
        $rows = $this->output()['assets']->map(fn(string $domain) => "<tr><td>{$domain}</td></tr>")->join("\n");
        return self::htmlTable($header, $rows, 1);
    }

    public function text(): string
    {
        return "{$this->output()['message']} The assets are : {$this->output()['assets']->join(", ")}.";
    }

    protected function schema2(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "discover_assets",
                "description" => "Discover subdomains of a root domain.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "domain" => [
                            "type" => ["string"],
                            "description" => "The root domain.",
                        ],
                    ],
                    "required" => [
                        "domain"
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
            $domain = $args['domain'] ?? null;

            $request = new Request();
            $request->replace([
                'domain' => $domain,
            ]);

            $controller = new AssetController();
            $response = $controller->discover($request);
            $count = count($response['subdomains']);

            $this->output = [
                "message" => "{$count} subdomains discovered.",
                "assets" => collect($response['subdomains']),
            ];

        } catch (\Exception $e) {
            $this->output = [
                "message" => $e->getMessage(),
                "assets" => collect(),
            ];
        }
        return $this;
    }
}
