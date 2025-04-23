<?php

namespace App\Helpers\LlmFunctions;

use App\Modules\AdversaryMeter\Models\Asset;
use App\Modules\AdversaryMeter\Models\Port;
use App\User;

class QueryOpenPortDatabase extends AbstractLlmFunction
{
    public function html(): string
    {
        $header = "
            <th>Asset</th>
            <th>IP</th>
            <th class='right'>Port</th>
            <th>Protocol</th>
            <th>Service</th>
            <th>Technologies</th>
        ";

        $rows = $this->output()
            ->map(function (Port $port) {

                $tags = $port->tags()
                    ->orderBy('tag')
                    ->get()
                    ->pluck('tag')
                    ->map(fn(string $tag) => "<span class='lozenge new'>{$tag}</span>")
                    ->join(" ");

                return "
                    <tr>
                      <td>{$port->hostname}</td>
                      <td>{$port->ip}</td>
                      <td class='right'>{$port->port}</td>
                      <td>{$port->protocol}</td>
                      <td>{$port->service}</td>
                      <td>{$tags}</td>
                    </tr>
                ";
            })
            ->join("\n");

        return self::htmlTable($header, $rows, 6);
    }

    public function text(): string
    {
        $output = $this->output();
        return $output->isEmpty() ? 'No open ports were found.'
            : $output->map(function (Port $port) {

                $tags = $port->tags()
                    ->orderBy('tag')
                    ->get()
                    ->pluck('tag')
                    ->join(", ");

                return "| {$port->hostname} | {$port->ip} | {$port->port} | {$port->protocol} | {$port->service} | {$tags} |";
            })
                ->prepend("| Asset | IP | Port | Protocol | Service | Technologies |")
                ->prepend("|---|---|---|---|---|---|")
                ->join("\n");
    }

    protected function schema2(): array
    {
        return [
            "type" => "function",
            "function" => [
                "name" => "query_open_port_database",
                "description" => "Query the open port database.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "asset" => [
                            "type" => ["string", "null"],
                            "description" => "The asset's IP address, domain or subdomain.",
                        ],
                        "port" => [
                            "type" => ["integer", "null"],
                            "description" => "The port number.",
                        ],
                        "service" => [
                            "type" => ["string", "null"],
                            "description" => "The service bind to the port.",
                        ],
                        "technologies" => [
                            "type" => ["array", "null"],
                            "description" => "One or more technology tags associated to the port.",
                        ],
                    ],
                    "required" => [],
                    "additionalProperties" => false,
                ],
                "strict" => true,
            ],
        ];
    }

    protected function handle2(User $user, string $threadId, array $args): AbstractLlmFunction
    {
        $asset = $args['asset'] ?? null;
        $port = $args['port'] ?? null;
        $service = $args['service'] ?? null;
        $tags = $args['technologies'] ?? null;

        if (empty($asset)) {
            $assets = Asset::all();
        } else {
            /** @var Asset $asset */
            $asset = Asset::where('asset', $asset)->firstOrFail();
            $assets = collect([$asset]);
        }

        if (isset($tags) && is_string($tags)) {
            $tags = [$tags];
        }

        $this->output = $assets->flatMap(function (Asset $asset) use ($port, $service, $tags) {

            $query = $asset->ports()
                ->where('closed', false)
                ->orderBy('port');

            if (!empty($port)) {
                $query->where('port', $port);
            }
            if (!empty($service)) {
                $query->where('service', $service);
            }
            return $query->get()->filter(fn(Port $port) => !isset($tags) ||
                count($tags) <= 0 ||
                $port->tags()->whereIn('tag', $tags)->exists()
            );
        });
        return $this;
    }
}
