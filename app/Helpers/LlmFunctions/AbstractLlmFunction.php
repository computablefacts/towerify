<?php

namespace App\Helpers\LlmFunctions;

use App\User;

abstract class AbstractLlmFunction
{
    protected mixed $output;
    protected string $text;
    protected string $html;

    public static function schema(): array
    {
        return [
            // (new QueryAssetDatabase)->schema2(),
            // (new QueryVulnerabilityDatabase())->schema2(),
            (new QueryIssp())->schema2(),
            (new BeginAssetMonitoring())->schema2(),
            (new EndAssetMonitoring())->schema2(),
            (new RemoveAsset())->schema2(),
            (new DiscoverAssets())->schema2(),
            // (new QueryOpenPortDatabase())->schema2(),
        ];
    }

    public static function handle(User $user, string $threadId, string $function, array $args): AbstractLlmFunction
    {
        return match ($function) {
            'query_asset_database' => (new QueryAssetDatabase())->handle2($user, $threadId, $args),
            'query_vulnerability_database' => (new QueryVulnerabilityDatabase())->handle2($user, $threadId, $args),
            'query_issp' => (new QueryIssp())->handle2($user, $threadId, $args),
            'begin_asset_monitoring' => (new BeginAssetMonitoring())->handle2($user, $threadId, $args),
            'end_asset_monitoring' => (new EndAssetMonitoring())->handle2($user, $threadId, $args),
            'remove_asset' => (new RemoveAsset())->handle2($user, $threadId, $args),
            'discover_assets' => (new DiscoverAssets())->handle2($user, $threadId, $args),
            'query_open_port_database' => (new QueryOpenPortDatabase())->handle2($user, $threadId, $args),
            default => throw new \Exception("Unknown function: {$function}"),
        };
    }

    public static function htmlTable(string $header, string $rows, int $nbCols): string
    {
        $rows = empty($rows) ? "<tr><td colspan='{$nbCols}' style='text-align: center'>No data available.</td></tr>" : $rows;
        return "
            <div class='tw-answer-table-wrapper'>
              <div class='tw-answer-table'>
                <table>
                  <thead>
                    <tr>
                      {$header}
                    </tr>
                  </thead>
                  <tbody>
                    {$rows}
                  </tbody>
                </table>
              </div>
            </div>        
        ";
    }

    public function output(): mixed
    {
        return $this->output;
    }

    public abstract function html(): string;

    public abstract function text(): string;

    public abstract function markdown(): string;

    protected abstract function schema2(): array;

    protected abstract function handle2(User $user, string $threadId, array $args): AbstractLlmFunction;
}
