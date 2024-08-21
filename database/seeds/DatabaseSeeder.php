<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Konekt\Address\Models\ZoneScope;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('countries')->count() <= 0) {
            $this->call(\Konekt\Address\Seeds\Countries::class);
        }
        $this->setupTenants();
        $this->setupPermissions();
        $this->setupRoles();
        $this->setupUsers();
        $this->setupPaymentMethods();
        $this->setupProductCategories();
        $this->setupProductProperties();
        $this->setupProducts();
        $this->setupOsqueryRules();
    }

    private function setupTenants(): void
    {
        //
    }

    private function setupPermissions(): void
    {
        // Remove support for legacy permissions
        \App\Models\Permission::where('name', 'configure ssh connections')->delete();
        \App\Models\Permission::where('name', 'configure app permissions')->delete();
        \App\Models\Permission::where('name', 'configure user apps')->delete();
        \App\Models\Permission::where('name', 'deploy apps')->delete();
        \App\Models\Permission::where('name', 'launch apps')->delete();
        \App\Models\Permission::where('name', 'send invitations')->delete();

        // Create missing permissions
        foreach (\App\Models\Role::ROLES as $role => $permissions) {
            foreach ($permissions as $permission) {
                $perm = \App\Models\Permission::firstOrCreate(
                    ['name' => $permission],
                    [
                        'name' => $permission,
                        'guard_name' => 'web',
                    ]
                );
            }
        }
    }

    private function setupRoles(): void
    {
        // Create missing roles
        foreach (\App\Models\Role::ROLES as $role => $permissions) {
            $role = \App\Models\Role::firstOrcreate([
                'name' => $role
            ]);
            foreach ($permissions as $permission) {
                $perm = \App\Models\Permission::where('name', $permission)->firstOrFail();
                $role->permissions()->syncWithoutDetaching($perm);
            }
        }
    }

    private function setupUsers(): void
    {
        // Create super admin
        $email = config('towerify.admin.email');
        $username = config('towerify.admin.username');
        $password = config('towerify.admin.password');
        $user = \App\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $username,
                'email' => $email,
                'password' => \App\Hashing\TwHasher::hash($password),
                'type' => 'admin',
                'is_active' => true,
            ]
        );

        // Add the 'admin' role to the user
        $admin = \App\Models\Role::where('name', \App\Models\Role::ADMIN)->first();

        if ($admin) {
            if (!DB::table('model_roles')
                ->where('role_id', $admin->id)
                ->where('model_id', $user->id)
                ->exists()) {
                DB::table('model_roles')
                    ->insert([
                        'role_id' => $admin->id,
                        'model_type' => \App\User::class,
                        'model_id' => $user->id,
                    ]);
            }
        }
    }

    private function setupPaymentMethods(): void
    {
        // Create zone
        $zoneIsEu = \App\Models\Zone::firstOrCreate(['name' => 'EU']);
        $zoneIsEu->scope = ZoneScope::TAXATION();
        \Konekt\Address\Models\Country::where('is_eu_member', true)
            ->get()
            ->each(function (\Konekt\Address\Models\Country $country) use ($zoneIsEu) {
                $zoneIsEu->addCountry($country);
            });
        $zoneIsEu->save();

        // Create tax category
        $taxCategoryIsVat = \App\Models\TaxCategory::firstOrCreate(
            ['name' => 'VAT'],
            [
                'is_active' => true,
            ]
        );

        // Create tax rate
        $taxRateForEuVat = \App\Models\TaxRate::firstOrCreate(
            ['name' => 'EU VAT'],
            [
                'zone_id' => $zoneIsEu->id,
                'tax_category_id' => $taxCategoryIsVat->id,
                'rate' => 20,
                'is_active' => true,
                'valid_from' => '2024-03-24',
                'valid_until' => null,
            ]
        );
        $taxRateForEuVat->tax_category_id = $taxCategoryIsVat->id;
        $taxRateForEuVat->save();

        // Create an 'offline' payment method
        $paymentMethod = \App\Models\PaymentMethod::firstOrCreate(
            ['name' => 'Offline'],
            [
                'name' => 'Offline',
                'gateway' => 'null',
                'is_enabled' => true,
            ]
        );
    }

    // See https://vanilo.io/docs/3.x/categorization for details
    private function setupProductCategories(): void
    {
        // Create the 'IT' product category
        $it = \App\Models\Taxonomy::where('name', \App\Models\Taxonomy::APPLICATIONS)->first();
        if (!$it) {
            $it = \App\Models\Taxonomy::firstOrCreate(['name' => \App\Models\Taxonomy::IT]);
        } else {
            $it->name = \App\Models\Taxonomy::IT;
            $it->save();
        }

        // Create the 'Business' product category
        $business = \App\Models\Taxonomy::where('name', \App\Models\Taxonomy::SERVERS)->first();
        if (!$business) {
            $business = \App\Models\Taxonomy::firstOrCreate(['name' => \App\Models\Taxonomy::BUSINESS]);
        } else {
            $business->name = \App\Models\Taxonomy::BUSINESS;
            $business->save();
        }

        // Load categories from the AppStore
        $priority = 1;

        foreach (\App\Helpers\AppStore::categories() as $category) {
            $tag = \App\Models\Taxon::updateOrCreate(
                ['name' => $category],
                [
                    'taxonomy_id' => $it->id,
                    'name' => $category,
                    'priority' => $priority++,
                ]
            );
        }

        // Set categories from the InfraStore
        $tag = \App\Models\Taxon::where('name', 'Baremetal')->first();
        if ($tag) {
            $tag->delete();
        }
    }

    private function setupProductProperties(): void
    {
        // Create the 'ram' product property
        $ram = \App\Models\Property::firstOrCreate(
            ['slug' => \App\Models\Property::RAM_SLUG],
            ['name' => 'RAM', 'slug' => \App\Models\Property::RAM_SLUG, 'type' => 'number']
        );

        // Create the 'cpu' product property
        $cpu = \App\Models\Property::firstOrCreate(
            ['slug' => \App\Models\Property::CPU_SLUG],
            ['name' => 'CPU', 'slug' => \App\Models\Property::CPU_SLUG, 'type' => 'number']
        );

        // Create the 'storage' product property
        $disk = \App\Models\Property::firstOrCreate(
            ['slug' => \App\Models\Property::STORAGE_SLUG],
            ['name' => 'Storage', 'slug' => \App\Models\Property::STORAGE_SLUG, 'type' => 'number']
        );
    }

    private function setupProducts(): void
    {
        // Load tax rate
        $taxCategory = \App\Models\TaxCategory::findByName('VAT');

        // Load apps from the AppStore
        foreach (\App\Helpers\AppStore::catalog() as $app) {

            $product = \App\Models\Product::updateOrCreate(
                ['sku' => $app['sku']],
                [
                    'name' => $app['name'],
                    'sku' => $app['sku'],
                    'description' => $app['description_fr'],
                    'state' => $app['state'],
                    'price' => $app['price'],
                    'original_price' => $app['original_price'],
                    'tax_category_id' => $taxCategory->id,
                ]
            );

            $product->clearMediaCollection();

            $media = $product
                ->copyMedia(public_path('/images/' . $app['logo']))
                ->toMediaCollection();

            $taxon = \App\Models\Taxon::where('name', $app['category'])
                ->get()
                ->firstOrFail();

            $product->taxons()->syncWithoutDetaching($taxon);
        }
    }

    private function setupOsqueryRules(): void
    {
        $rules = $this->palantirOsqueryRulesForLinux()['schedule'];
        foreach ($rules as $name => $rule) {
            $fields = [];
            if (isset($rule['description'])) {
                $fields['description'] = $rule['description'];
            }
            if (isset($rule['query'])) {
                $fields['query'] = $rule['query'];
            }
            if (isset($rule['interval'])) {
                $fields['interval'] = $rule['interval'];
            }
            if (isset($rule['removed'])) {
                $fields['removed'] = $rule['removed'];
            }
            if (isset($rule['snapshot'])) {
                $fields['snapshot'] = $rule['snapshot'];
            }
            if (isset($rule['platform'])) {
                $fields['platform'] = $rule['platform'];
            }
            \App\Models\YnhOsqueryRule::updateOrCreate(['name' => $name], $fields);
        }
        $rules = $this->customRules();
        foreach ($rules as $rule) {
            $fields = [];
            if (isset($rule['description'])) {
                $fields['description'] = $rule['description'];
            }
            if (isset($rule['query'])) {
                $fields['query'] = $rule['query'];
            }
            if (isset($rule['interval'])) {
                $fields['interval'] = $rule['interval'];
            }
            if (isset($rule['removed'])) {
                $fields['removed'] = $rule['removed'];
            }
            if (isset($rule['snapshot'])) {
                $fields['snapshot'] = $rule['snapshot'];
            }
            if (isset($rule['platform'])) {
                $fields['platform'] = $rule['platform'];
            }
            \App\Models\YnhOsqueryRule::updateOrCreate(['name' => $rule['name']], $fields);
        }
    }

    private function customRules(): array
    {
        return [[
            'name' => "packages_available_snapshot",
            'query' => "SELECT name, version, source FROM deb_packages;",
            'description' => "Display all installed DEB packages.",
            'interval' => 86400,
            'snapshot' => true,
        ], [
            'name' => "memory_available_snapshot",
            'query' => "SELECT printf('%.2f',((memory_total - memory_available) * 1.0)/1073741824) AS used_space_gb, printf('%.2f',(1.0 * memory_available / 1073741824)) AS space_left_gb, printf('%.2f',(1.0 * memory_total / 1073741824)) AS total_space_gb, printf('%.2f',(((memory_total - memory_available) * 1.0)/1073741824)/(1.0 * memory_total / 1073741824)) * 100 AS '%_used', printf('%.2f',(1.0 * memory_available / 1073741824)/(1.0 * memory_total / 1073741824)) * 100 AS '%_available' FROM memory_info;",
            'description' => "Track memory usage.",
            'interval' => 300,
            'snapshot' => true,
        ], [
            'name' => "disk_available_snapshot",
            'query' => "SELECT printf('%.2f',((blocks - blocks_available * 1.0) * blocks_size)/1073741824) AS used_space_gb, printf('%.2f',(1.0 * blocks_available * blocks_size / 1073741824)) AS space_left_gb, printf('%.2f',(1.0 * blocks * blocks_size / 1073741824)) AS total_space_gb, printf('%.2f',(((blocks - blocks_available * 1.0) * blocks_size)/1073741824)/(1.0 * blocks * blocks_size / 1073741824)) * 100 AS '%_used', printf('%.2f',(1.0 * blocks_available * blocks_size / 1073741824)/(1.0 * blocks * blocks_size / 1073741824)) * 100 AS '%_available' FROM mounts WHERE path = '/';",
            'description' => "Track disk usage.",
            'interval' => 300,
            'snapshot' => true,
        ]];
    }

    private function palantirOsqueryRulesForLinux(): array
    {
        // See https://github.com/palantir/osquery-configuration/blob/master/Classic/Servers/Linux/osquery.conf for details
        $rules = <<< EOF
{
  "options": {
    "logger_snapshot_event_type": "true",
    "schedule_splay_percent": 10
  },
  "platform": "linux",
  "schedule": {
    "process_events": {
      "query": "SELECT auid, cmdline, ctime, cwd, egid, euid, gid, parent, path, pid, time, uid FROM process_events WHERE path NOT IN ('/bin/sed', '/usr/bin/tr', '/bin/gawk', '/bin/date', '/bin/mktemp', '/usr/bin/dirname', '/usr/bin/head', '/usr/bin/jq', '/bin/cut', '/bin/uname', '/bin/basename') and cmdline NOT LIKE '%_key%' AND cmdline NOT LIKE '%secret%';",
      "interval": 10,
      "description": "Process events collected from the audit framework."
    },
    "socket_events": {
      "query": "SELECT action, auid, family, local_address, local_port, path, pid, remote_address, remote_port, success, time FROM socket_events WHERE success=1 AND path NOT IN ('/usr/bin/hostname') AND remote_address NOT IN ('127.0.0.1', '169.254.169.254', '', '0000:0000:0000:0000:0000:0000:0000:0001', '::1', '0000:0000:0000:0000:0000:ffff:7f00:0001', 'unknown', '0.0.0.0', '0000:0000:0000:0000:0000:0000:0000:0000');",
      "interval": 10,
      "description": "Socket events collected from the audit framework."
    },
    "file_events": {
      "query": "SELECT * FROM file_events;",
      "interval": 10,
      "description": "File events collected from file integrity monitoring.",
      "removed": false
    },
    "apt_sources": {
      "query": "SELECT * FROM apt_sources;",
      "interval": 86400,
      "description": "Display apt package manager sources.",
      "snapshot": true,
      "platform": "ubuntu"
    },
    "authorized_keys": {
      "query": "SELECT * FROM users CROSS JOIN authorized_keys USING (uid);",
      "interval": 86400,
      "description": "A line-delimited authorized_keys table."
    },
    "behavioral_reverse_shell": {
      "query": "SELECT DISTINCT(processes.pid), processes.parent, processes.name, processes.path, processes.cmdline, processes.cwd, processes.root, processes.uid, processes.gid, processes.start_time, process_open_sockets.remote_address, process_open_sockets.remote_port, (SELECT cmdline FROM processes AS parent_cmdline WHERE pid=processes.parent) AS parent_cmdline FROM processes JOIN process_open_sockets USING (pid) LEFT OUTER JOIN process_open_files ON processes.pid = process_open_files.pid WHERE (name='sh' OR name='bash') AND remote_address NOT IN ('0.0.0.0', '::', '') AND remote_address NOT LIKE '10.%' AND remote_address NOT LIKE '192.168.%';",
      "interval": 600,
      "description": "Find shell processes that have open sockets."
    },
    "cpu_time": {
      "query": "SELECT * FROM cpu_time;",
      "interval": 3600,
      "description": "Displays information from /proc/stat file about the time the CPU cores spent in different parts of the system."
    },
    "crontab": {
      "query": "SELECT * FROM crontab;",
      "interval": 3600,
      "description": "Retrieves all the jobs scheduled in crontab in the target system."
    },
    "crontab_snapshot": {
      "query": "SELECT * FROM crontab;",
      "interval": 86400,
      "description": "Retrieves all the jobs scheduled in crontab in the target system.",
      "snapshot": true
    },
    "deb_packages": {
      "query": "SELECT * FROM deb_packages;",
      "interval": 86400,
      "description": "Display all installed DEB packages.",
      "snapshot": true,
      "platform": "ubuntu"
    },
    "dns_resolvers": {
      "query": "SELECT * FROM dns_resolvers;",
      "interval": 3600,
      "description": "DNS resolvers used by the host."
    },
    "ec2_instance_metadata": {
      "query": "SELECT * FROM ec2_instance_metadata;",
      "interval": 3600,
      "description": "Retrieve the EC2 metadata for this endpoint."
    },
    "ec2_instance_metadata_snapshot": {
      "query": "SELECT * FROM ec2_instance_metadata;",
      "interval": 86400,
      "description": "Snapshot query to retrieve the EC2 metadata for this endpoint.",
      "snapshot": true
    },
    "ec2_instance_tags": {
      "query": "SELECT * FROM ec2_instance_tags;",
      "interval": 3600,
      "description": "Retrieve the EC2 tags for this endpoint."
    },
    "ec2_instance_tags_snapshot": {
      "query": "SELECT * FROM ec2_instance_tags;",
      "interval": 86400,
      "description": "Snapshot query to retrieve the EC2 tags for this instance.",
      "snapshot": true
    },
    "etc_hosts": {
      "query": "SELECT * FROM etc_hosts;",
      "interval": 3600,
      "description": "Retrieves all the entries in the target system /etc/hosts file."
    },
    "etc_hosts_snapshot": {
      "query": "SELECT * FROM etc_hosts;",
      "interval": 86400,
      "description": "Retrieves all the entries in the target system /etc/hosts file.",
      "snapshot": true
    },
    "hardware_events": {
      "query": "SELECT * FROM hardware_events;",
      "description": "Track hardware events.",
      "interval": 10,
      "removed": false
    },
    "iptables": {
      "query": "SELECT * FROM iptables;",
      "interval": 86400,
      "platform": "linux",
      "description": "Retrieves the current filters and chains per filter in the target system."
    },
    "kernel_info": {
      "query": "SELECT * FROM kernel_info;",
      "interval": 86400,
      "description": "Retrieves information from the current kernel in the target system.",
      "snapshot": true
    },
    "kernel_integrity": {
      "query": "SELECT * FROM kernel_integrity;",
      "interval": 86400,
      "description": "Various Linux kernel integrity checked attributes."
    },
    "kernel_modules": {
      "query": "SELECT * FROM kernel_modules;",
      "interval": 3600,
      "description": "Linux kernel modules both loaded and within the load search path."
    },
    "kernel_modules_snapshot": {
      "query": "SELECT * FROM kernel_modules;",
      "interval": 86400,
      "description": "Linux kernel modules both loaded and within the load search path.",
      "snapshot": true
    },
    "last": {
      "query": "SELECT * FROM last;",
      "interval": 3600,
      "description": "Retrieves the list of the latest logins with PID, username and timestamp."
    },
    "ld_preload": {
      "query": "SELECT process_envs.pid, process_envs.key, process_envs.value, processes.name, processes.path, processes.cmdline, processes.cwd FROM process_envs join processes USING (pid) WHERE key = 'LD_PRELOAD';",
      "interval": 60,
      "description": "Any processes that run with an LD_PRELOAD environment variable.",
      "snapshot": true
    },
    "ld_so_preload_exists": {
      "query": "SELECT * FROM file WHERE path='/etc/ld.so.preload' AND path!='';",
      "interval": 3600,
      "description": "Generates an event if ld.so.preload is present - used by rootkits such as Jynx.",
      "snapshot": true
    },
    "listening_ports": {
      "query": "SELECT pid, port, processes.path, cmdline, cwd FROM listening_ports JOIN processes USING (pid) WHERE port!=0;",
      "interval": 86400,
      "description": "Gather information about processes that are listening on a socket.",
      "snapshot": true
    },
    "memory_info": {
      "query": "SELECT * FROM memory_info;",
      "interval": 3600,
      "description": "Information about memory usage on the system."
    },
    "mounts": {
      "query": "SELECT device, device_alias, path, type, blocks_size, flags FROM mounts;",
      "interval": 86400,
      "description": "Retrieves the current list of mounted drives in the target system."
    },
    "network_interfaces_snapshot": {
      "query": "SELECT a.interface, a.address, d.mac FROM interface_addresses a JOIN interface_details d USING (interface);",
      "interval": 600,
      "description": "Record the network interfaces and their associated IP and MAC addresses.",
      "snapshot": true
    },
    "os_version": {
      "query": "SELECT * FROM os_version;",
      "interval": 86400,
      "description": "Retrieves information from the Operating System where osquery is currently running.",
      "snapshot": true
    },
    "osquery_info": {
      "query": "SELECT * FROM osquery_info;",
      "interval": 86400,
      "description": "Information about the running osquery configuration.",
      "snapshot": true
    },
    "processes_snapshot": {
      "query": "select name, path, cmdline, cwd, on_disk from processes;",
      "interval": 86400,
      "description": "A snapshot of all processes running on the host. Useful for outlier analysis.",
      "snapshot": true
    },
    "rpm_packages": {
      "query": "SELECT name, version, release, arch FROM rpm_packages;",
      "interval": 86400,
      "description": "Display all installed RPM packages.",
      "snapshot": true,
      "platform": "centos"
    },
    "runtime_perf": {
      "query": "SELECT ov.version AS os_version, ov.platform AS os_platform, ov.codename AS os_codename, i.*, p.resident_size, p.user_time, p.system_time, time.minutes AS counter, db.db_size_mb AS database_size from osquery_info i, os_version ov, processes p, time, (SELECT (SUM(size) / 1024) / 1024.0 AS db_size_mb FROM (SELECT value FROM osquery_flags WHERE name = 'database_path' LIMIT 1) flags, file WHERE path LIKE flags.value || '%%' AND type = 'regular') db WHERE p.pid = i.pid;",
      "interval": 1800,
      "description": "Records system/user time, db size, and many other system metrics."
    },
    "shell_history": {
      "query": "SELECT * FROM users CROSS JOIN shell_history USING (uid);",
      "interval": 3600,
      "description": "Record shell history for all users on system (instead of just root)."
    },
    "suid_bin": {
      "query": "SELECT * FROM suid_bin;",
      "interval": 86400,
      "description": "Display any SUID binaries that are owned by root."
    },
    "system_info": {
      "query": "SELECT * FROM system_info;",
      "interval": 86400,
      "description": "Information about the system hardware and name.",
      "snapshot": true
    },
    "usb_devices": {
      "query": "SELECT * FROM usb_devices;",
      "interval": 120,
      "description": "Retrieves the current list of USB devices in the target system."
    },
    "user_ssh_keys": {
      "query": "SELECT * FROM users CROSS JOIN user_ssh_keys USING (uid);",
      "interval": 86400,
      "description": "Returns the private keys in the users ~/.ssh directory and whether or not they are encrypted."
    },
    "users": {
      "query": "SELECT * FROM users;",
      "interval": 86400,
      "description": "Local system users."
    },
    "users_snapshot": {
      "query": "SELECT * FROM users;",
      "interval": 86400,
      "description": "Local system users.",
      "snapshot": true
    },
    "yum_sources": {
      "query": "SELECT name, baseurl, enabled, gpgcheck FROM yum_sources;",
      "interval": 86400,
      "description": "Display yum package manager sources.",
      "snapshot": true,
      "platform": "centos"
    }
  },
  "file_paths": {
    "configuration": [
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
    "binaries": [
      "/usr/bin/%%",
      "/usr/sbin/%%",
      "/bin/%%",
      "/sbin/%%",
      "/usr/local/bin/%%",
      "/usr/local/sbin/%%"
    ]
  },
  "events": {
    "disable_subscribers": [
      "user_events"
    ]
  },
  "packs": {
    "ossec-rootkit": "/etc/osquery/packs/ossec-rootkit.conf"
  }
}
EOF;
        return json_decode($rules, true);
    }
}
