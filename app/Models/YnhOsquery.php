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

    public static function configLogParser(YnhServer $server): string
    {
        return <<< EOT
#!/bin/bash

if [ -d /var/log/nginx ]; then
  while read file; do
    if [[ "\$file" == *.gz ]]; then
      zcat "\$file" | awk -v fname="\$file" 'function basename(file,a,n){n=split(file,a,"/");return a[n]}BEGIN{fname=basename(fname);if(fname=="access.log"){sub(/.log/,"",fname)}else{sub(/-access.*/,"",fname)}}{print fname" "$1}'
    else
      cat "\$file" | awk -v fname="\$file" 'function basename(file,a,n){n=split(file,a,"/");return a[n]}BEGIN{fname=basename(fname);if(fname=="access.log"){sub(/.log/,"",fname)}else{sub(/-access.*/,"",fname)}}{print fname" "$1}'
    fi
  done< <(find /var/log/nginx -type f -name '*-access.log*') | sort | uniq -c | awk '$1 >= 3' | sort -nr | gzip -c >/opt/logparser/nginx.txt.gz
  curl -X POST \
    -H "Content-Type: multipart/form-data" \
    -F "data=@/opt/logparser/nginx.txt.gz" \
    https://app.towerify.io/logparser/{$server->secret}
fi

EOT;
    }

    public static function configLogAlert(YnhServer $server): array
    {
        return ["monitors" => [
            [
                "name" => "Monitor Osquery Daemon Output",
                "path" => "/var/log/osquery/osqueryd.*.log",
                "match" => ".*",
                "regexp" => true,
                "url" => "https://app.towerify.io/logalert/{$server->secret}"
            ]
        ],
            "sleep" => 5,
            "echo" => false,
            "verbose" => 1
        ];
    }

    public static function configOsquery(): array
    {
        $schedule = [];
        YnhOsqueryRule::orderBy('name', 'asc')
            ->get()
            ->each(function (YnhOsqueryRule $rule) use (&$schedule) {
                $schedule[$rule->name] = [
                    'query' => $rule->query,
                    'interval' => $rule->interval,
                    'removed' => $rule->removed,
                    'snapshot' => $rule->snapshot,
                    'platform' => $rule->platform->value,
                ];
                if ($rule->version) {
                    $schedule[$rule->name]['version'] = $rule->version;
                }
            });
        return [
            "options" => [
                "logger_snapshot_event_type" => "true",
                "schedule_splay_percent" => 10
            ],
            "platform" => "linux",
            "schedule" => $schedule,
            "file_paths" => [
                "configuration" => [
                    "/etc/passwd",
                    "/etc/shadow",
                    "/etc/ld.so.preload",
                    "/etc/ld.so.conf",
                    "/etc/ld.so.conf.d/%%",
                    "/etc/pam.d/%%",
                    "/etc/resolv.conf",
                    "/etc/rc%/%%",
                    "/etc/my.cnf",
                    "/etc/modules",
                    "/etc/hosts",
                    "/etc/hostname",
                    "/etc/fstab",
                    "/etc/crontab",
                    "/etc/cron%/%%",
                    "/etc/init/%%",
                    "/etc/rsyslog.conf"
                ],
                "binaries" => [
                    "/usr/bin/%%",
                    "/usr/sbin/%%",
                    "/bin/%%",
                    "/sbin/%%",
                    "/usr/local/bin/%%",
                    "/usr/local/sbin/%%"
                ]
            ],
            "events" => [
                "disable_subscribers" => [
                    "user_events"
                ]
            ],
            "packs" => [],
        ];
    }

    public static function installLogAlertAndOsquery(YnhServer $server): string
    {
        return <<<EOT
#!/bin/bash

apt install wget curl tmux jq -y

# Install Osquery
if [ ! -f /etc/osquery/osquery.conf ]; then
    wget https://pkg.osquery.io/deb/osquery_5.11.0-1.linux_amd64.deb
    apt install ./osquery_5.11.0-1.linux_amd64.deb
    rm osquery_5.11.0-1.linux_amd64.deb
fi

# Install LogParser
if [ ! -f /opt/logparser/parser ]; then 
  mkdir -p /opt/logparser
fi

# Install LogAlert
if [ ! -f /opt/logalert/config.json ]; then 
  mkdir -p /opt/logalert
  curl -L https://github.com/jhuckaby/logalert/releases/latest/download/logalert-linux-x64 >/opt/logalert/logalert.bin
  chmod 755 /opt/logalert/logalert.bin
fi

# LEGACY CODE BEGINS HERE
sudo -H -u root bash -c 'tmux kill-ses -t forward-results'
sudo -H -u root bash -c 'tmux kill-ses -t forward-snapshots'

if [ -f /etc/osquery/forward-results.sh ]; then
  rm -f /etc/osquery/forward-results.sh
fi
if [ -f /etc/osquery/forward-snapshots.sh ]; then
  rm -f /etc/osquery/forward-snapshots.sh
fi
# LEGACY CODE ENDS HERE

# Stop LogAlert then Osquery
sudo -H -u root bash -c 'tmux kill-ses -t logalert'
osqueryctl stop osqueryd

# Update LogAlert configuration
wget -O /opt/logalert/config2.json https://app.towerify.io/logalert/{$server->secret}

if jq empty /opt/logalert/config2.json; then
  mv -f /opt/logalert/config2.json /opt/logalert/config.json
fi

# Update LogParser configuration
wget -O /opt/logparser/parser2 https://app.towerify.io/logparser/{$server->secret}

if { bash -n /opt/logparser/parser2; } then
  mv -f /opt/logparser/parser2 /opt/logparser/parser
  chmod +x /opt/logparser/parser
fi

# Update Osquery configuration
wget -O /etc/osquery/osquery2.conf https://app.towerify.io/osquery/{$server->secret}

if jq empty /etc/osquery/osquery2.conf; then
  mv -f /etc/osquery/osquery2.conf /etc/osquery/osquery.conf
fi

# Parse web logs every hour
cat <(fgrep -i -v '/opt/logparser/parser' <(crontab -l)) <(echo '0 * * * * /opt/logparser/parser') | crontab -

# Drop Osquery daemon's output every sunday at 01:00 am
cat <(fgrep -i -v 'rm /var/log/osquery/osqueryd.results.log /var/log/osquery/osqueryd.snapshots.log' <(crontab -l)) <(echo '0 1 * * 0 rm /var/log/osquery/osqueryd.results.log /var/log/osquery/osqueryd.snapshots.log') | crontab -

# Drop LogAlert's logs every day at 01:00 am
cat <(fgrep -i -v 'rm /opt/logalert/log.txt' <(crontab -l)) <(echo '0 1 * * * rm /opt/logalert/log.txt') | crontab -

# Start Osquery then LogAlert 
osqueryctl start osqueryd
sudo -H -u root bash -c 'tmux new-session -A -d -s logalert'
tmux send-keys -t logalert "/opt/logalert/logalert.bin" C-m

EOT;
    }

    public static function osInfos(Collection $servers): Collection
    {
        // {
        //      "arch":"x86_64",
        //      "build":null,
        //      "codename":"bullseye",
        //      "major":"11",
        //      "minor":"0",
        //      "name":"Debian GNU\/Linux",
        //      "patch":"0",
        //      "platform":"debian",
        //      "platform_like":null,
        //      "version":"11 (bullseye)"
        // }
        return $servers->isEmpty() ? collect() : collect(DB::select("
            SELECT DISTINCT 
                ynh_osquery.ynh_server_id,
                ynh_servers.name AS ynh_server_name,
                TIMESTAMP(ynh_osquery.calendar_time - SECOND(ynh_osquery.calendar_time)) AS `timestamp`,
                json_unquote(json_extract(ynh_osquery.columns, '$.arch')) AS architecture,
                json_unquote(json_extract(ynh_osquery.columns, '$.codename')) AS codename,
                CAST(json_unquote(json_extract(ynh_osquery.columns, '$.major')) AS INTEGER) AS major_version,
                CAST(json_unquote(json_extract(ynh_osquery.columns, '$.minor')) AS INTEGER) AS minor_version,
                json_unquote(json_extract(ynh_osquery.columns, '$.platform')) AS os,
                CASE
                  WHEN json_unquote(json_extract(ynh_osquery.columns, '$.patch')) = 'null' THEN NULL
                  ELSE CAST(json_unquote(json_extract(ynh_osquery.columns, '$.patch')) AS INTEGER)
                END AS patch_version
            FROM ynh_osquery
            INNER JOIN (
              SELECT 
                ynh_server_id, MAX(calendar_time) AS calendar_time 
              FROM ynh_osquery 
              WHERE name = 'os_version'
              GROUP BY ynh_server_id
            ) AS t ON t.ynh_server_id = ynh_osquery.ynh_server_id AND t.calendar_time = ynh_osquery.calendar_time
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'os_version'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
        "));
    }

    public static function memoryUsage(Collection $servers, int $limit = 1000): Collection
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
                FROM ynh_osquery_memory_usage

                ORDER BY timestamp DESC
                LIMIT {$limit}
            ) AS t
            INNER JOIN ynh_servers ON ynh_servers.id = t.ynh_server_id
            WHERE ynh_servers.id IN ({$servers->pluck('id')->join(',')})
            ORDER BY t.timestamp ASC;
        "));
    }

    public static function diskUsage(Collection $servers, int $limit = 1000): Collection
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
                FROM ynh_osquery_disk_usage

                ORDER BY timestamp DESC
                LIMIT {$limit}
            ) AS t
            INNER JOIN ynh_servers ON ynh_servers.id = t.ynh_server_id
            WHERE ynh_servers.id IN ({$servers->pluck('id')->join(',')}) 
            ORDER BY t.timestamp ASC;
        "));
    }

    public static function users(Collection $servers, int $limit): Collection
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
            LIMIT {$limit};
        "));
    }

    public static function loginsAndLogouts(Collection $servers, int $limit): Collection
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
                CASE
                  WHEN json_unquote(json_extract(ynh_osquery.columns, '$.host')) = 'null' THEN NULL
                  ELSE json_unquote(json_extract(ynh_osquery.columns, '$.host'))
                END AS entry_host,
                json_unquote(json_extract(ynh_osquery.columns, '$.time')) AS entry_timestamp,
                json_unquote(json_extract(ynh_osquery.columns, '$.tty')) AS entry_terminal,
                json_unquote(json_extract(ynh_osquery.columns, '$.type_name')) AS entry_type,
                CASE
                    WHEN json_unquote(json_extract(ynh_osquery.columns, '$.username')) = 'null' THEN NULL
                    ELSE json_unquote(json_extract(ynh_osquery.columns, '$.username'))
                END AS entry_username,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'last'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC, entry_timestamp DESC
            LIMIT {$limit};
        "));
    }

    public static function suidBinaries(Collection $servers, int $limit): Collection
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
            LIMIT {$limit};
        "));
    }

    public static function kernelModules(Collection $servers, int $limit): Collection
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
            LIMIT {$limit}
        "));
    }

    public static function authorizedKeys(Collection $servers, int $limit): Collection
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
                CASE
                  WHEN json_unquote(json_extract(ynh_osquery.columns, '$.comment')) = 'null' THEN NULL
                  ELSE json_unquote(json_extract(ynh_osquery.columns, '$.comment'))
                END AS key_comment,
                json_unquote(json_extract(ynh_osquery.columns, '$.algorithm')) AS algorithm,
                ynh_osquery.action
            FROM ynh_osquery
            INNER JOIN ynh_servers ON ynh_servers.id = ynh_osquery.ynh_server_id
            WHERE ynh_osquery.name = 'authorized_keys'
            AND ynh_osquery.ynh_server_id IN ({$servers->pluck('id')->join(',')})
            ORDER BY timestamp DESC
            LIMIT {$limit}
        "));
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class);
    }
}
