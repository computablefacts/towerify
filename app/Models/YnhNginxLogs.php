<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property int id
 * @property bool updated
 * @property ?int from_ynh_server_id
 * @property ?int to_ynh_server_id
 * @property string from_ip_address
 * @property string to_ip_address
 * @property string service
 * @property int weight
 */
class YnhNginxLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_ynh_server_id',
        'to_ynh_server_id',
        'from_ip_address',
        'to_ip_address',
        'service',
        'weight',
        'updated',
    ];

    protected $casts = [
        'updated' => 'boolean',
    ];

    public static function interdependencies(Collection $servers, ?YnhServer $centeredAroundServer = null): array
    {
        $adversaryMeterIpAddresses = collect(config('towerify.adversarymeter.ip_addresses'))->join('\',\'');
        $ids = $servers->pluck('id')->join(',');
        $nodes = $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT
              ynh_servers.name AS label
            FROM ynh_nginx_logs 
            INNER JOIN ynh_servers ON ynh_servers.id = from_ynh_server_id
            WHERE from_ynh_server_id IN ({$ids})
            AND from_ip_address NOT IN ('{$adversaryMeterIpAddresses}') 

            UNION DISTINCT

            SELECT 
              ynh_servers.name AS label
            FROM ynh_nginx_logs 
            INNER JOIN ynh_servers ON ynh_servers.id = to_ynh_server_id
            WHERE to_ynh_server_id IN ({$ids})
        "))->map(function (object $node) {
            $nodeId = 'n' . preg_replace("/[^A-Za-z0-9]/", '', $node->label);
            return [
                'data' => [
                    'id' => $nodeId,
                    'label' => $node->label,
                    'color' => '#f8b500',
                ],
            ];
        });

        // Log::debug($nodes);

        $center = $centeredAroundServer ? "AND (from_ynh_server_id = {$centeredAroundServer->id} OR to_ynh_server_id = {$centeredAroundServer->id})" : "";
        $edges = $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT
              CASE 
                WHEN source.name IS NULL THEN from_ip_address 
                ELSE source.name 
              END AS src,
              target.name AS dest,
              GROUP_CONCAT(service SEPARATOR '|') AS services
            FROM ynh_nginx_logs
            INNER JOIN ynh_servers AS source ON source.id = from_ynh_server_id
            INNER JOIN ynh_servers AS target ON target.id = to_ynh_server_id
            WHERE from_ip_address NOT IN ('{$adversaryMeterIpAddresses}')
            AND from_ynh_server_id IN ({$ids})
            AND to_ynh_server_id IN ({$ids})
            {$center}
            GROUP BY src, dest
        "))->map(function (object $edge) {
            $srcNodeId = 'n' . preg_replace("/[^A-Za-z0-9]/", '', $edge->src);
            $destNodeId = 'n' . preg_replace("/[^A-Za-z0-9]/", '', $edge->dest);
            return [
                'data' => [
                    'id' => $srcNodeId . $destNodeId,
                    'source' => $srcNodeId,
                    'target' => $destNodeId,
                    'services' => explode('|', $edge->services),
                ],
            ];
        });

        // Log::debug($edges);

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'from_ynh_server_id', 'id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'to_ynh_server_id', 'id');
    }
}
