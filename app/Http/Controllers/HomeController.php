<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\YnhOrder;
use App\Models\YnhServer;
use App\Models\YnhSshTraces;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'my-apps');
        $user = Auth::user();
        $servers = YnhServer::forUser($user);
        $orders = YnhOrder::forUser($user);
        $memory_usage = collect();
        $disk_usage = collect();

        if ($tab === 'resources_usage') {
            $memory_usage = $this->memoryUsage($servers)->groupBy('ynh_server_name');
            $disk_usage = $this->diskUsage($servers)->groupBy('ynh_server_name');
        }

        $security_events = collect();

        if ($tab === 'security') {
            $security_events = [
                'authorized_keys' => $this->securityEventsAuthorizedKeys($servers),
                'kernel_modules' => $this->securityEventsKernelModules($servers),
                'suid_bin' => $this->securityEventsSuidBin($servers),
                'last_logins_and_logouts' => $this->securityEventsLastLoginsAndLogouts($servers),
                'users' => $this->securityEventsUsers($servers),
            ];
        }

        $interdependencies = collect();

        if ($tab === 'interdependencies') {
            $interdependencies = $this->interdependencies($servers);
        }

        $invitations = collect();

        if ($tab === 'invitations') {
            $invitations = Invitation::whereNull('user_id')->get();
        }

        $pendingActions = collect();
        $traces = collect();

        if ($tab === 'traces') {
            $pendingActions = $servers->flatMap(fn(YnhServer $server) => $server->pendingActions())
                ->sortBy([
                    fn(YnhSshTraces $a, YnhSshTraces $b) => $a->updated_at->diffInMilliseconds($b->updated_at),
                    fn(YnhSshTraces $a, YnhSshTraces $b) => strcmp($a->server->name, $b->server->name),
                ]);
            $traces = $servers->flatMap(fn(YnhServer $server) => $server->latestTraces())
                ->sortBy([
                    fn(YnhSshTraces $a, YnhSshTraces $b) => strcmp($a->server->name, $b->server->name),
                    fn(YnhSshTraces $a, YnhSshTraces $b) => $b->order - $a->order,
                ]);
        }

        $users = collect();

        if ($tab === 'users') {
            if ($user->tenant_id) {
                $users = User::where('is_active', true)->where('tenant_id', $user->tenant_id);
                if ($user->customer_id) {
                    $users = $users->where('customer_id', $user->customer_id);
                }
                $users = $users->get();
            } else {
                $users = User::where('is_active', true)->get();
            }
        }

        $domains = collect();

        if ($tab === 'domains') {
            $domains = $servers->flatMap(fn(YnhServer $server) => $server->domains);
        }

        $applications = collect();

        if ($tab === "applications") {
            $applications = $servers->flatMap(fn(YnhServer $server) => $server->applications);
        }

        $backups = collect();
        if ($tab === 'backups') {
            $backups = $servers->flatMap(fn(YnhServer $server) => $server->backups);
        }
        return view('home.index', compact('tab', 'servers', 'orders', 'users', 'invitations', 'memory_usage', 'disk_usage', 'security_events', 'interdependencies', 'traces', 'pendingActions', 'domains', 'applications', 'backups'));
    }

    private function memoryUsage(Collection $servers): Collection
    {
        $minDate = Carbon::today()->subDays(2);
        return $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT 
              ynh_servers.name AS ynh_server_name, 
              t.* 
            FROM (
                SELECT 
                    ynh_osquery.ynh_server_id,
                    TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_available'))), 2) AS percent_available,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_used'))), 2) AS percent_used,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.space_left_gb'))), 2) AS space_left_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.total_space_gb'))), 2) AS total_space_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.used_space_gb'))), 2) AS used_space_gb
                FROM ynh_osquery
                WHERE ynh_osquery.name = 'memory_available_snapshot'
                AND ynh_osquery.calendar_time >= '{$minDate->toDateString()}'
                GROUP BY ynh_osquery.ynh_server_id, ynh_osquery.calendar_time
                ORDER BY timestamp DESC
                LIMIT 1000
            ) AS t
            INNER JOIN ynh_servers ON ynh_servers.id = t.ynh_server_id
            WHERE ynh_servers.id IN ({$servers->pluck('id')->join(',')})
            ORDER BY t.timestamp ASC;
        "));
    }

    private function diskUsage(Collection $servers): Collection
    {
        $minDate = Carbon::today()->subDays(2);
        return $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT
              ynh_servers.name AS ynh_server_name,
              t.*
            FROM (
                SELECT 
                    ynh_osquery.ynh_server_id,
                    TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_available'))), 2) AS percent_available,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.%_used'))), 2) AS percent_used,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.space_left_gb'))), 2) AS space_left_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.total_space_gb'))), 2) AS total_space_gb,
                    ROUND(AVG(json_unquote(json_extract(ynh_osquery.columns, '$.used_space_gb'))), 2) AS used_space_gb
                FROM ynh_osquery
                WHERE ynh_osquery.name = 'disk_available_snapshot'
                AND ynh_osquery.calendar_time >= '{$minDate->toDateString()}'
                GROUP BY ynh_osquery.ynh_server_id, ynh_osquery.calendar_time
                ORDER BY timestamp DESC
                LIMIT 1000
            ) AS t
            INNER JOIN ynh_servers ON ynh_servers.id = t.ynh_server_id
            WHERE ynh_servers.id IN ({$servers->pluck('id')->join(',')}) 
            ORDER BY t.timestamp ASC;
        "));
    }

    private function securityEventsUsers(Collection $servers): Collection
    {
        // {
        //      "description":null,
        //      "directory":"\/var\/www\/ocr-irve_dev",
        //      "gid":"969",
        //      "gid_signed":"969",
        //      "shell":"\/bin\/sh",
        //      "uid":"970",
        //      "uid_signed":"970",
        //      "username":"ocr-irve_dev",
        //      "uuid":null
        // }
        return $servers->isEmpty() ? collect() : collect(DB::select("
          SELECT DISTINCT 
                ynh_osquery.ynh_server_id,
                ynh_servers.name AS ynh_server_name,
                TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.uid')) AS user_id,    
                json_unquote(json_extract(ynh_osquery.columns, '$.gid')) AS group_id,
                json_unquote(json_extract(ynh_osquery.columns, '$.username')) AS username,
                json_unquote(json_extract(ynh_osquery.columns, '$.directory')) AS home_directory,    
                json_unquote(json_extract(ynh_osquery.columns, '$.shell')) AS default_shell,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'users'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
            LIMIT 20;
        "));
    }

    private function securityEventsLastLoginsAndLogouts(Collection $servers): Collection
    {
        // {
        //      "host":null,
        //      "pid":"791077",
        //      "time":"1709559920",
        //      "tty":"pts\/1",
        //      "type":"8",
        //      "type_name":"dead-process",
        //      "username":"root"
        // }
        return $servers->isEmpty() ? collect() : collect(DB::select("
          SELECT DISTINCT 
                ynh_osquery.ynh_server_id,
                ynh_servers.name AS ynh_server_name,
                TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.pid')) AS pid,    
                json_unquote(json_extract(ynh_osquery.columns, '$.host')) AS entry_host,
                json_unquote(json_extract(ynh_osquery.columns, '$.time')) AS entry_timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.tty')) AS entry_terminal,
                json_unquote(json_extract(ynh_osquery.columns, '$.type_name')) AS entry_type,
                json_unquote(json_extract(ynh_osquery.columns, '$.username')) AS entry_username,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'last'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
            LIMIT 20;
        "));
    }

    private function securityEventsSuidBin(Collection $servers): Collection
    {
        // {
        //      "groupname":"tty",
        //      "path":"\/usr\/bin\/write",
        //      "permissions":"G",
        //      "username":"root"
        // }
        return $servers->isEmpty() ? collect() : collect(DB::select("
          SELECT DISTINCT 
                ynh_osquery.ynh_server_id,
                ynh_servers.name AS ynh_server_name,
                TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.path')) AS `path`,
                json_unquote(json_extract(ynh_osquery.columns, '$.groupname')) AS groupname,
                json_unquote(json_extract(ynh_osquery.columns, '$.username')) AS username,
                json_unquote(json_extract(ynh_osquery.columns, '$.permissions')) AS permissions,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'suid_bin'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
            LIMIT 20;
        "));
    }

    private function securityEventsKernelModules(Collection $servers): Collection
    {
        // {
        //      "address":"0xffffffffc0223000",
        //      "name":"virtio_scsi",
        //      "size":"24576",
        //      "status":"Live",
        //      "used_by":"-"
        // }
        return $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT DISTINCT 
                ynh_osquery.ynh_server_id,
                ynh_servers.name AS ynh_server_name,
                TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.name')) AS `name`,
                json_unquote(json_extract(ynh_osquery.columns, '$.address')) AS address,
                json_unquote(json_extract(ynh_osquery.columns, '$.size')) AS `size`,
                json_unquote(json_extract(ynh_osquery.columns, '$.status')) AS status,
                json_unquote(json_extract(ynh_osquery.columns, '$.used_by')) AS used_by,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'kernel_modules'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
            LIMIT 20
        "));
    }

    private function securityEventsAuthorizedKeys(Collection $servers): Collection
    {
        // {
        //      "algorithm":"ssh-rsa",
        //      "comment":"patrick@SpectreMate",
        //      "description":"root",
        //      "directory":"\/root",
        //      "gid":"0",
        //      "gid_signed":"0",
        //      "key":"AAAAB3NzaC1yc2EAAAADAQABAAABAQDah7RARA035UA5H4lsaLBb4tqIkFZBv318ZVZmuFHvzAnO3nX4Ze81xucMirxBo6udrtVcH28IPOurYSqHXSaPjxGkptRo2cVA1I1qjJMWjlgmNcjHfrfjRK4+zr+EY9VUIYqbSoRmRowWb6N2WrulOWJct0adQ47ZFEY9XpxZG2raAk2dkSjBioNBuc+3U9SSfLvFmkhU\/Jek7+G8S\/CGXWUG42R2XcmovgeW136LB9FASnITYXkJOt0jgPmhPpYlteHWP1Su3pOP1lpbyF4nqPpgdHYDqIYJkzHYV4XDWLj9GWlHJtpIug076cZ32+WE4GYOD4kvbIOJbYr4I+y\/",
        //      "key_file":"\/root\/.ssh\/authorized_keys",
        //      "options":null,
        //      "shell":"\/bin\/bash",
        //      "uid":"0",
        //      "uid_signed":"0",
        //      "username":"root",
        //      "uuid":null
        // }
        return $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT DISTINCT 
                ynh_osquery.ynh_server_id,
                ynh_servers.name AS ynh_server_name,
                TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.key_file')) AS key_file,
                json_unquote(json_extract(ynh_osquery.columns, '$.key')) AS `key`,
                json_unquote(json_extract(ynh_osquery.columns, '$.comment')) AS key_comment,
                json_unquote(json_extract(ynh_osquery.columns, '$.algorithm')) AS algorithm,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'authorized_keys'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
            LIMIT 20
        "));
    }

    private function interdependencies(Collection $servers): array
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
}
