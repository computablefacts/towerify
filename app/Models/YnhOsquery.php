<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int ynh_server_id
 * @property int row
 * @property string name
 * @property string host_identifier
 * @property Carbon calendar_time
 * @property int unix_time
 * @property int epoch
 * @property int counter
 * @property bool numerics
 * @property array columns
 * @property string action
 * @property bool packed
 * @property bool dismissed
 */
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
        'dismissed',
    ];

    protected $casts = [
        'numerics' => 'boolean',
        'columns' => 'array',
        'calendar_time' => 'datetime',
        'packed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dismissed' => 'boolean',
    ];

    public static function configLogParser(YnhServer $server): string
    {
        $url = app_url();
        return <<< EOT
#!/bin/bash

if [ -f /etc/os-release ]; then

    id_like=$(grep '^ID_LIKE=' /etc/os-release | cut -d'=' -f2 | tr -d '"')
    
    if [ -z "\$id_like" ]; then
      id_like=$(grep '^ID=' /etc/os-release | cut -d'=' -f2 | tr -d '"')
    fi

    # Ensure that the OS is debian-based
    if [[ "\$id_like" == *"debian"* ]]; then
    
        # Parse Nginx logs
        if [ -d /etc/nginx ]; then
          for conf_file in "/etc/nginx/"{sites-available,conf.d}"/"*; do
            if [ -f "\$conf_file" ]; then
            
              server_name=$(grep -E "^\s*server_name\s+" "\$conf_file" | awk '{print $2}' | tr -d ';' | head -1)
              access_log_info=$(grep -E "^\s*access_log\s+" "\$conf_file")
            
              if [ -z "\$server_name" ]; then
                server_name="vhost.unk"
              fi
              if [ -n "\$access_log_info" ]; then
            
                access_log_path=$(echo "\$access_log_info" | awk '{print $2}' | tr -d ';' | head -1)
                log_format=$(echo "\$access_log_info" | awk '{print $3}' | tr -d ';' | head -1)
            
                if [ "\$log_format" == "combined" ] || [ "\$log_format" == "" ]; then
                  while read file; do
                    if [[ "\$file" == *.gz ]]; then
                      zcat "\$file" | awk -v fname="\$server_name" '{print fname" "$1}'
                    else
                      cat "\$file" | awk -v fname="\$server_name" '{print fname" "$1}'
                    fi
                  done< <(find "$(dirname \$access_log_path)" -type f -name "$(basename \$access_log_path)*")
                fi
              fi
            fi
          done | sort | uniq -c | awk '$1 >= 3' | sort -nr | gzip -c >/opt/logparser/nginx.txt.gz
          
          if [ -f /opt/logparser/nginx.txt.gz ]; then
            curl -X POST \
              -H "Content-Type: multipart/form-data" \
              -F "data=@/opt/logparser/nginx.txt.gz" \
              {$url}/logparser/{$server->secret}
          fi
        fi
        
        # Parse Apache logs
        if [ -d /etc/apache2 ] && [ -d /opt/logparser ]; then
          if [ -f /etc/apache2/envvars ]; then
            apache_log_dir=$(grep -R "APACHE_LOG_DIR" /etc/apache2/envvars | awk -F'=' '{print $2}' | awk -F'$' '{print $1}')
          else
            apache_log_dir="/var/log/apache2"
          fi
          for conf_file in "/etc/apache2/sites-available/"*; do
            if [ -f "\$conf_file" ]; then
        
              server_name=$(grep -E "^\s*ServerName\s+" "\$conf_file" | awk '{print $2}')
              server_alias=$(grep -E "^\s*ServerAlias\s+" "\$conf_file" | awk '{print $2}')
              custom_log_info=$(grep -E "^\s*CustomLog\s+" "\$conf_file")
        
              if [ -z "\$server_name" ]; then
                server_name="\$server_alias"
              fi
              if [ -z "\$server_name" ]; then
                server_name="vhost.unk"
              fi
              if [ -n "\$custom_log_info" ]; then
        
                custom_log_path=$(echo "\$custom_log_info" | awk '{print $2}' | tr -d '"' | sed "s|\\\${APACHE_LOG_DIR}|\$apache_log_dir|g")
                log_format=$(echo "\$custom_log_info" | awk '{print $3}')
        
                if [ "\$log_format" == "combined" ] || [ "\$log_format" == "common" ] || [ -z "\$log_format" ]; then
                  while read file; do
                    if [[ "\$file" == *.gz ]]; then
                      zcat "\$file" | awk -v fname="\$server_name" '{print fname" "$1}'
                    else
                      cat "\$file" | awk -v fname="\$server_name" '{print fname" "$1}'
                    fi
                  done< <(find "$(dirname \$custom_log_path)" -type f -name "$(basename \$custom_log_path)*")
                fi
              fi
            fi
          done | sort | uniq -c | awk '$1 >= 3' | sort -nr | gzip -c >/opt/logparser/apache.txt.gz
          
          if [ -f /opt/logparser/apache.txt.gz ]; then
            curl -X POST \
              -H "Content-Type: multipart/form-data" \
              -F "data=@/opt/logparser/apache.txt.gz" \
              {$url}/logparser/{$server->secret}
          fi
        fi
        
        # Get the list of installed packages
        if [ -d /opt/logparser ]; then
          
          # Get it only once a day
          if ! find "/opt/logparser/osquery.jsonl.gz" -mtime -1 | grep -q "/opt/logparser/osquery.jsonl.gz"; then
          
            apt_packages=$(apt list --installed 2>/dev/null | awk -F'[ /]' '{print $1 " " $3 " " $4 " apt"}' | tail -n +2)
            snap_packages=$(snap list 2>/dev/null | awk 'NR>1 {print $1 " " $2 " " $3 " snap"}')
            dpkg_packages=$(dpkg-query -W -f='\${binary:Package} \${Version} \${Architecture} dpkg\\n' 2>/dev/null)
            all_packages=$(echo -e "\$apt_packages\\n\$snap_packages\\n\$dpkg_packages" | sort -u)
        
            echo "\$all_packages" | awk '{
              key = $1 " " $2 " " $3
              if (key in seen) {
                seen[key] = seen[key] "," $4
              } else {
                seen[key] = $4
              }
            } END {
              for (key in seen) {
                print key " " seen[key]
              }
            }' \
            | sort \
            | awk -v hostname="$(hostname)" -v epoch="$(date +'%s')" -v date="$(LC_TIME=C date +'%a %b %e %T %Y %Z')" -v uid="$(tr -dc A-Za-z0-9 </dev/urandom | head -c 15; echo)" '{print "{\"row\":0,\"name\":\"deb_packages_installed_snapshot\",\"hostIdentifier\":\""hostname"\",\"calendarTime\":\""date"\",\"unixTime\":\""epoch"\",\"epoch\":0,\"counter\":0,\"numerics\":0,\"action\":\"snapshot\",\"columns\":{\"uid\":\""uid"\",\"name\":\""$1"\",\"version\":\""$2"\",\"arch\":\""$3"\",\"manager\":\""$4"\",\"status\":\"installed\"}}"}' \
            | gzip -c >/opt/logparser/osquery.jsonl.gz
            
            if [ -f /opt/logparser/osquery.jsonl.gz ]; then
              curl -X POST \
                -H "Content-Type: multipart/form-data" \
                -F "data=@/opt/logparser/osquery.jsonl.gz" \
                {$url}/logparser/{$server->secret}
            fi
          fi
        fi
    fi
fi

EOT;
    }

    public static function configLogAlert(YnhServer $server): array
    {
        $url = app_url();
        return ["monitors" => [
            [
                "name" => "Monitor Osquery Daemon Output",
                "path" => "/var/log/osquery/osqueryd.*.log",
                "match" => ".*",
                "regexp" => true,
                "url" => "{$url}/logalert/{$server->secret}"
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
        YnhOsqueryRule::where('enabled', true)
            ->orderBy('name', 'asc')
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
            "events" => [],
            "packs" => [],
        ];
    }

    public static function monitorServer(YnhServer $server): string
    {
        $url = app_url();
        $whitelist = collect(config('towerify.adversarymeter.ip_addresses'))
            ->map(fn(string $ip) => "sed -i '/^ignoreip/ { /{$ip}/! s/$/ {$ip}/ }' /etc/fail2ban/jail.conf")
            ->join("\n");
        return <<<EOT
#!/bin/bash

apt install wget curl jq -y

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

# See commit #12408bd3 for details
if [ -d /opt/logparser ] && [ ! -f /opt/logparser/12408bd3.jsonl.gz ]; then

  # Backup existing file
  if [ -f /opt/logparser/osquery.jsonl.gz ]; then
    mv /opt/logparser/osquery.jsonl.gz /opt/logparser/osquery-tmp.jsonl.gz
  fi
  
  # Parse history to get back dropped events after commit #1bd199b3
  cat /var/log/osquery/osqueryd.results.log \
    | grep -Eai '"(groups|dns_resolvers|etc_services|python_packages|interface_addresses|startup_items|certificates|process_listening_port)"' \
    | gzip -c >/opt/logparser/osquery.jsonl.gz
  
  # Send dropped events to the server
  curl -X POST \
    -H "Content-Type: multipart/form-data" \
    -F "data=@/opt/logparser/osquery.jsonl.gz" \
    {$url}/logparser/{$server->secret}
  
  # Set marker in order to prevent re-execution  
  mv /opt/logparser/osquery.jsonl.gz /opt/logparser/12408bd3.jsonl.gz
  
  # Restore backup 
  if [ -f /opt/logparser/osquery-tmp.jsonl.gz ]; then
    mv /opt/logparser/osquery-tmp.jsonl.gz /opt/logparser/osquery.jsonl.gz
  fi
fi

# Stop Osquery then LogAlert because reloading resets LogAlert internal state (see https://github.com/jhuckaby/logalert for details)  
osqueryctl stop osqueryd

# An attempt at dealing with https://github.com/osquery/osquery/issues/8064
sleep 20

tmux has-session -t "logalert" 2>/dev/null

if [ $? != 0 ]; then
  systemctl stop logalert
else
  sudo -H -u root bash -c 'tmux kill-ses -t logalert'
fi

# Update LogAlert configuration
wget -O /opt/logalert/config2.json {$url}/logalert/{$server->secret}

if [ -s /opt/logalert/config2.json ]; then
  if jq empty /opt/logalert/config2.json; then
    mv -f /opt/logalert/config2.json /opt/logalert/config.json
  fi
else
  rm /opt/logalert/config2.json
fi

# Update LogParser configuration
wget -O /opt/logparser/parser2 {$url}/logparser/{$server->secret}

if [ -s /opt/logparser/parser2 ]; then
  if { bash -n /opt/logparser/parser2; } then
    mv -f /opt/logparser/parser2 /opt/logparser/parser
    chmod +x /opt/logparser/parser
  fi
else
    rm /opt/logparser/parser2
fi

# Set LogAlert as a daemon
echo '[Unit]' > /etc/systemd/system/logalert.service
echo 'Description=LogAlert (cywise)' >> /etc/systemd/system/logalert.service
echo '[Service]' >> /etc/systemd/system/logalert.service
echo 'ExecStart=/opt/logalert/logalert.bin' >> /etc/systemd/system/logalert.service
echo '[Install]' >> /etc/systemd/system/logalert.service
echo 'WantedBy=multi-user.target' >> /etc/systemd/system/logalert.service

# Update Osquery configuration
wget -O /etc/osquery/osquery2.conf {$url}/osquery/{$server->secret}

if [ -s /etc/osquery/osquery2.conf ]; then
  if jq empty /etc/osquery/osquery2.conf; then
    mv -f /etc/osquery/osquery2.conf /etc/osquery/osquery.conf
  fi
else
  rm /etc/osquery/osquery2.conf
fi

# Set Osquery flags
echo '--disable_events=false' > /etc/osquery/osquery.flags # overwrite file!
echo '--enable_file_events=true' >> /etc/osquery/osquery.flags
echo '--audit_allow_config=true' >> /etc/osquery/osquery.flags
echo '--audit_allow_sockets' >> /etc/osquery/osquery.flags
echo '--audit_persist=true' >> /etc/osquery/osquery.flags
echo '--disable_audit=false' >> /etc/osquery/osquery.flags
echo '--events_expiry=1' >> /etc/osquery/osquery.flags
echo '--events_max=500000' >> /etc/osquery/osquery.flags
echo '--logger_min_status=1' >> /etc/osquery/osquery.flags
echo '--logger_plugin=filesystem' >> /etc/osquery/osquery.flags
echo '--watchdog_memory_limit=350' >> /etc/osquery/osquery.flags
echo '--watchdog_utilization_limit=130' >> /etc/osquery/osquery.flags

# Parse web logs every hour
cat <(fgrep -i -v '/opt/logparser/parser' <(crontab -l)) <(echo '0 * * * * /opt/logparser/parser') | crontab -

# Drop Osquery daemon's output every sunday at 01:11 am
cat <(fgrep -i -v 'rm /var/log/osquery/osqueryd.results.log /var/log/osquery/osqueryd.snapshots.log' <(crontab -l)) <(echo '11 1 * * 0 rm /var/log/osquery/osqueryd.results.log /var/log/osquery/osqueryd.snapshots.log') | crontab -

# Drop LogAlert's logs every day at 02:22 am
cat <(fgrep -i -v 'rm /opt/logalert/log.txt' <(crontab -l)) <(echo '22 2 * * * rm /opt/logalert/log.txt') | crontab -

# Auto-update the server every day at 03:33 am
cat <(fgrep -i -v 'curl -s {$url}/update/{$server->secret} | bash' <(crontab -l)) <(echo '33 3 * * * curl -s {$url}/update/{$server->secret} | bash') | crontab -

# Delete entry that call old domain app.towerify.io
crontab -l | grep -v "app\.towerify\.io" | crontab -

# Start LogAlert then Osquery because reloading resets LogAlert internal state (see https://github.com/jhuckaby/logalert for details)  
# sudo -H -u root bash -c 'tmux new-session -A -d -s logalert'
# tmux send-keys -t logalert "/opt/logalert/logalert.bin" C-m
systemctl start logalert
osqueryctl start osqueryd

# If fail2ban is up-and-running, whitelist AdversaryMeter IP addresses
if systemctl is-active --quiet fail2ban; then
  if [ -f /etc/fail2ban/jail.conf ]; then
    {$whitelist}
    systemctl restart fail2ban
  fi
fi

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

    public static function suspiciousEvents(Collection $servers, Carbon $cutOffTime): Collection
    {
        return YnhOsquery::where('calendar_time', '>=', $cutOffTime)
            ->whereIn('name', [
                'authorized_keys',
                'last',
                'users',
                'suid_bin',
                'ld_preload',
                'kernel_modules',
                'crontab',
                'etc_hosts',
                'mounts',
                'shell_check',
                'sudoers_shell',
                'sudoers_sha1',
                'deb_packages',
            ])
            ->where('dismissed', false)
            ->whereIn('ynh_server_id', $servers->pluck('id'))
            ->orderBy('calendar_time', 'desc')
            ->get()
            ->map(function (YnhOsquery $event) {
                if ($event->name === 'authorized_keys') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Une clef SSH a été ajoutée au trousseau de l'utilisateur {$event->columns['username']}.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Une clef SSH a été supprimée du trousseau de l'utilisateur {$event->columns['username']}.",
                        ];
                    }
                } elseif ($event->name === 'last') {
                    if ($event->action === 'added') {
                        if ($event->columns['type_name'] === 'user-process') {
                            return [
                                'id' => $event->id,
                                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                                'server' => $event->server->name,
                                'ip' => $event->server->ip(),
                                'message' => "L'utilisateur {$event->columns['username']} s'est connecté au serveur."
                            ];
                        }
                    }
                } elseif ($event->name === 'users') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "L'utilisateur {$event->columns['username']} a été créé.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "L'utilisateur {$event->columns['username']} a été supprimé.",
                        ];
                    }
                } elseif ($event->name === 'suid_bin') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Les privilèges du binaire {$event->columns['path']} ont été élevés.",
                        ];
                    }
                } elseif ($event->name === 'ld_preload') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le binaire {$event->columns['value']} a été ajouté à la variable d'environnement LD_PRELOAD.",
                        ];
                    }
                } elseif ($event->name === 'kernel_modules') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le module {$event->columns['name']} a été ajouté au noyau.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le module {$event->columns['name']} a été enlevé du noyau.",
                        ];
                    }
                } elseif ($event->name === 'crontab') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Une tâche planifiée a été ajoutée: {$event->columns['command']}",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Une tâche planifiée a été supprimée: {$event->columns['command']}",
                        ];
                    }
                } elseif ($event->name === 'etc_hosts') {
                    if ($event->action === 'added') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le traffic réseau vers {$event->columns['hostnames']} est maintenant redirigé vers {$event->columns['address']}.",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le traffic réseau vers {$event->columns['hostnames']} n'est maintenant plus redirigé vers {$event->columns['address']}.",
                        ];
                    }
                } elseif ($event->name === 'mounts') {

                    $isDockerMountEvent = (Str::startsWith($event->columns['path'], '/var/lib/docker/') && $event->columns['type'] === 'overlay') ||
                        (Str::startsWith($event->columns['path'], '/run/docker/') && $event->columns['type'] === 'nsfs');

                    if (!$isDockerMountEvent) { // drop Docker-generated 'mounts' events
                        /* if ($event->action === 'added') {
                            return [
                                'id' => $event->id,
                                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                                'server' => $event->server->name,
                                'ip' => $event->server->ip(),
                                'message' => "Le répertoire {$event->columns['path']} pointe maintenant vers un système de fichiers de type {$event->columns['type']}.",
                            ];
                        } elseif ($event->action === 'removed') {
                            return [
                                'id' => $event->id,
                                'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                                'server' => $event->server->name,
                                'ip' => $event->server->ip(),
                                'message' => "Le répertoire {$event->columns['path']} ne pointe maintenant plus vers un système de fichiers de type {$event->columns['type']}.",
                            ];
                        } */
                    }
                } elseif ($event->name === 'shell_check' || $event->name === 'sudoers_shell' || $event->name === 'sudoers_sha1') {
                    return [
                        'id' => $event->id,
                        'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                        'server' => $event->server->name,
                        'ip' => $event->server->ip(),
                        'message' => "Possible \"reverse shell\" (bash) transféré à un attaquant!",
                    ];
                } elseif ($event->name === 'deb_packages') {
                    if ($event->action === 'added') {

                        $osInfo = YnhOsquery::osInfos(collect([$event->server]))->first();

                        if (!$osInfo) {
                            $cves = '';
                        } else {
                            $cves = YnhCve::appCves($osInfo->os, $osInfo->codename, $event->columns['name'], $event->columns['version'])
                                ->pluck('cve')
                                ->join(', ');
                        }

                        $warning = empty($cves) ? '' : "Attention, ce paquet est vulnérable: {$cves}.";

                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le paquet {$event->columns['name']} ({$event->columns['version']}) a été installé. {$warning}",
                        ];
                    } elseif ($event->action === 'removed') {
                        return [
                            'id' => $event->id,
                            'timestamp' => $event->calendar_time->format('Y-m-d H:i:s'),
                            'server' => $event->server->name,
                            'ip' => $event->server->ip(),
                            'message' => "Le paquet {$event->columns['name']} ({$event->columns['version']}) a été désinstallé.",
                        ];
                    }
                }
                return [];
            })
            ->filter(fn(array $event) => count($event) >= 1);
    }

    public static function suspiciousMetrics(Collection $servers, Carbon $cutOffTime): Collection
    {
        return $servers->map(function (YnhServer $server) use ($cutOffTime) {

            /** @var YnhOsquery $metric */
            $metric = YnhOsquery::where('calendar_time', '>=', $cutOffTime)
                ->where('name', 'disk_available_snapshot')
                ->where('ynh_server_id', $server->id)
                ->orderBy('calendar_time', 'desc')
                ->first();

            if ($metric && $metric->columns['%_available'] <= 20) {
                return [
                    'id' => $metric->id,
                    'timestamp' => $metric->calendar_time->format('Y-m-d H:i:s'),
                    'server' => $metric->server->name,
                    'ip' => $metric->server->ip(),
                    'message' => "Il vous reste {$metric->columns['%_available']}% d'espace disque disponible, soit {$metric->columns['space_left_gb']} Gb.",
                ];
            }
            return [];
        })
            ->filter(fn(array $metric) => count($metric) >= 1);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(YnhServer::class, 'ynh_server_id', 'id');
    }
}
