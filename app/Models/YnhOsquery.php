<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class YnhOsquery extends Model
{
    use HasFactory;

    protected $table = 'ynh_osquery';

    protected $fillable = [
        'ynh_server_id',
        'row',
        'name',
        'host_identifier',
        'calendar_time',
        'unix_time',
        'epoch',
        'counter',
        'numerics',
        'columns',
        'action',
        'packed',
    ];

    protected $casts = [
        'numerics' => 'boolean',
        'columns' => 'array',
        'calendar_time' => 'datetime',
        'packed' => 'boolean',
    ];

    public static function memoryUsage(Collection $servers): Collection
    {
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
                AND ynh_osquery.packed = 1
                GROUP BY ynh_osquery.ynh_server_id, ynh_osquery.calendar_time

                UNION

                SELECT 
                  ynh_server_id,
                  timestamp,
                  percent_available,
                  percent_used,
                  space_left_gb,
                  total_space_gb,
                  used_space_gb
                FROM ynh_memory_usage

                ORDER BY timestamp DESC
                LIMIT 1000
            ) AS t
            INNER JOIN ynh_servers ON ynh_servers.id = t.ynh_server_id
            WHERE ynh_servers.id IN ({$servers->pluck('id')->join(',')})
            ORDER BY t.timestamp ASC;
        "));
    }

    public static function diskUsage(Collection $servers): Collection
    {
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
                AND ynh_osquery.packed = 1
                GROUP BY ynh_osquery.ynh_server_id, ynh_osquery.calendar_time

                UNION

                SELECT 
                  ynh_server_id,
                  timestamp,
                  percent_available,
                  percent_used,
                  space_left_gb,
                  total_space_gb,
                  used_space_gb
                FROM ynh_disk_usage

                ORDER BY timestamp DESC
                LIMIT 1000
            ) AS t
            INNER JOIN ynh_servers ON ynh_servers.id = t.ynh_server_id
            WHERE ynh_servers.id IN ({$servers->pluck('id')->join(',')}) 
            ORDER BY t.timestamp ASC;
        "));
    }

    public static function usersSecurityEvents(Collection $servers): Collection
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

    public static function lastLoginsAndLogoutsSecurityEvents(Collection $servers): Collection
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

    public static function suidBinSecurityEvents(Collection $servers): Collection
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

    public static function kernelModulesSecurityEvents(Collection $servers): Collection
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

    public static function authorizedKeysSecurityEvents(Collection $servers): Collection
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

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }
}
