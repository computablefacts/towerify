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
        $rules = $this->hardwareMonitoringRules()['queries'];
        foreach ($rules as $name => $rule) {
            $this->addOrUpdateOsqueryRule($name, 'hardware monitoring', $rule);
        }

        $rules = $this->incidentResponseRules()['queries'];
        foreach ($rules as $name => $rule) {
            $this->addOrUpdateOsqueryRule($name, 'incident response', $rule);
        }

        $rules = $this->itComplianceRules()['queries'];
        foreach ($rules as $name => $rule) {
            $this->addOrUpdateOsqueryRule($name, 'it compliance', $rule);
        }

        $rules = $this->osqueryMonitoringRules()['queries'];
        foreach ($rules as $name => $rule) {
            $this->addOrUpdateOsqueryRule($name, 'osquery monitoring', $rule);
        }

        $rules = $this->ossecRootkitRules()['queries'];
        foreach ($rules as $name => $rule) {
            $this->addOrUpdateOsqueryRule($name, 'os security', $rule);
        }

        $rules = $this->vulnManagementRules()['queries'];
        foreach ($rules as $name => $rule) {
            $this->addOrUpdateOsqueryRule($name, 'vuln. management', $rule);
        }

        $rules = $this->customRules();
        foreach ($rules as $rule) {
            $this->addOrUpdateOsqueryRule($rule['name'], 'custom', $rule);
        }
    }

    private function addOrUpdateOsqueryRule(string $name, string $category, array $rule): void
    {
        $fields = [
            'name' => $name,
            'category' => $category,
        ];
        if (isset($rule['description'])) {
            $fields['description'] = $rule['description'];
        }
        if (isset($rule['value'])) {
            $fields['value'] = $rule['value'];
        }
        if (isset($rule['version'])) {
            $fields['version'] = $rule['version'];
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
        ], [
            'name' => "authorized_keys",
            'query' => "SELECT * FROM users CROSS JOIN authorized_keys USING (uid);",
            'description' => "A line-delimited authorized_keys table.",
            'interval' => 86400,
        ], [
            'name' => "behavioral_reverse_shell",
            'query' => "SELECT DISTINCT(processes.pid), processes.parent, processes.name, processes.path, processes.cmdline, processes.cwd, processes.root, processes.uid, processes.gid, processes.start_time, process_open_sockets.remote_address, process_open_sockets.remote_port, (SELECT cmdline FROM processes AS parent_cmdline WHERE pid=processes.parent) AS parent_cmdline FROM processes JOIN process_open_sockets USING (pid) LEFT OUTER JOIN process_open_files ON processes.pid = process_open_files.pid WHERE (name='sh' OR name='bash') AND remote_address NOT IN ('0.0.0.0', '::', '') AND remote_address NOT LIKE '10.%' AND remote_address NOT LIKE '192.168.%';",
            'description' => "Find shell processes that have open sockets.",
            'interval' => 600,
        ], [
            'name' => "cpu_time",
            "query" => "SELECT * FROM cpu_time;",
            "interval" => 3600,
            "description" => "Displays information from /proc/stat file about the time the CPU cores spent in different parts of the system.",
        ], [
            'name' => "crontab_snapshot",
            "query" => "SELECT * FROM crontab;",
            "interval" => 86400,
            "description" => "Retrieves all the jobs scheduled in crontab in the target system.",
            "snapshot" => true
        ], [
            'name' => "dns_resolvers",
            "query" => "SELECT * FROM dns_resolvers;",
            "interval" => 3600,
            "description" => "DNS resolvers used by the host.",
        ], [
            'name' => "ec2_instance_metadata",
            "query" => "SELECT * FROM ec2_instance_metadata;",
            "interval" => 3600,
            "description" => "Retrieve the EC2 metadata for this endpoint."
        ], [
            'name' => "ec2_instance_metadata_snapshot",
            "query" => "SELECT * FROM ec2_instance_metadata;",
            "interval" => 86400,
            "description" => "Snapshot query to retrieve the EC2 metadata for this endpoint.",
            "snapshot" => true
        ], [
            'name' => "ec2_instance_tags",
            "query" => "SELECT * FROM ec2_instance_tags;",
            "interval" => 3600,
            "description" => "Retrieve the EC2 tags for this endpoint."
        ], [
            'name' => "ec2_instance_tags_snapshot",
            "query" => "SELECT * FROM ec2_instance_tags;",
            "interval" => 86400,
            "description" => "Snapshot query to retrieve the EC2 tags for this instance",
            "snapshot" => true
        ], [
            'name' => 'etc_hosts_snapshot',
            "query" => "SELECT * FROM etc_hosts;",
            "interval" => 3600,
            "description" => "Retrieves all the entries in the target system /etc/hosts file."
        ], [
            'name' => 'file_events',
            "query" => "SELECT * FROM file_events;",
            "interval" => 10,
            "description" => "File events collected from file integrity monitoring",
            "removed" => false
        ], [
            'name' => 'kernel_integrity',
            "query" => "SELECT * FROM kernel_integrity;",
            "interval" => 86400,
            "description" => "Various Linux kernel integrity checked attributes."
        ], [
            'name' => "kernel_modules_snapshot",
            "query" => "SELECT * FROM kernel_modules;",
            "interval" => 86400,
            "platform" => "linux",
            "version" => "1.4.5",
            "description" => "Retrieves the current list of loaded kernel modules in the target Linux system.",
            "value" => "General security posture.",
            "snapshot" => true
        ], [
            'name' => 'ld_preload',
            "query" => "SELECT process_envs.pid, process_envs.key, process_envs.value, processes.name, processes.path, processes.cmdline, processes.cwd FROM process_envs JOIN processes USING (pid) WHERE KEY = 'LD_PRELOAD';",
            "interval" => 60,
            "description" => "Any processes that run with an LD_PRELOAD environment variable.",
            "snapshot" => true
        ], [
            'name' => 'ld_so_preload_exists',
            "query" => "SELECT * FROM file WHERE path='/etc/ld.so.preload' AND path!='';",
            "interval" => 3600,
            "description" => "Generates an event if ld.so.preload is present - used by rootkits such as Jynx.",
            "snapshot" => true
        ], [
            'name' => 'memory_info',
            "query" => "SELECT * FROM memory_info;",
            "interval" => 3600,
            "description" => "Information about memory usage on the system."
        ], [
            'name' => 'network_interfaces_snapshot',
            "query" => "SELECT a.interface, a.address, d.mac FROM interface_addresses a JOIN interface_details d USING (interface);",
            "interval" => 600,
            "description" => "Record the network interfaces and their associated IP and MAC addresses.",
            "snapshot" => true
        ], [
            'name' => 'processes_snapshot',
            "query" => "SELECT name, path, cmdline, cwd, on_disk FROM processes;",
            "interval" => 86400,
            "description" => "A snapshot of all processes running on the host. Useful for outlier analysis.",
            "snapshot" => true
        ], [
            'name' => 'process_events',
            "query" => "SELECT auid, cmdline, ctime, cwd, egid, euid, gid, parent, path, pid, TIME, uid FROM process_events WHERE PATH NOT IN ('/bin/sed', '/usr/bin/tr', '/bin/gawk', '/bin/date', '/bin/mktemp', '/usr/bin/dirname', '/usr/bin/head', '/usr/bin/jq', '/bin/cut', '/bin/uname', '/bin/basename') AND cmdline NOT LIKE '%_key%' AND cmdline NOT LIKE '%secret%';",
            "interval" => 10,
            "description" => "Process events collected from the audit framework."
        ], [
            'name' => 'runtime_perf',
            "query" => "SELECT ov.version AS os_version, ov.platform AS os_platform, ov.codename AS os_codename, i.*, p.resident_size, p.user_time, p.system_time, time.minutes AS counter, db.db_size_mb AS database_size FROM osquery_info i, os_version ov, processes p, time, (SELECT (SUM(SIZE) / 1024) / 1024.0 AS db_size_mb FROM (SELECT value FROM osquery_flags WHERE name = 'database_path' LIMIT 1) flags, file WHERE path LIKE flags.value || '%%' AND type = 'regular') db WHERE p.pid = i.pid;",
            "interval" => 1800,
            "description" => "Records system/user time, db size, and many other system metrics."
        ], [
            'name' => 'socket_events',
            "query" => "SELECT ACTION, auid, family, local_address, local_port, PATH, pid, remote_address, remote_port, success, TIME FROM socket_events WHERE success=1 AND PATH NOT IN ('/usr/bin/hostname') AND remote_address NOT IN ('127.0.0.1', '169.254.169.254', '', '0000:0000:0000:0000:0000:0000:0000:0001', '::1', '0000:0000:0000:0000:0000:ffff:7f00:0001', 'unknown', '0.0.0.0', '0000:0000:0000:0000:0000:0000:0000:0000');",
            "interval" => 10,
            "description" => "Socket events collected from the audit framework."
        ], [
            'name' => 'system_info',
            "query" => "SELECT * FROM system_info;",
            "interval" => 86400,
            "description" => "Information about the system hardware and name.",
            "snapshot" => true
        ], [
            'name' => 'users',
            "query" => "SELECT * FROM users;",
            "interval" => 86400,
            "description" => "Local system users."
        ], [
            'name' => 'users_snapshot',
            "query" => "SELECT * FROM users;",
            "interval" => 86400,
            "description" => "Local system users.",
            "snapshot" => true
        ], [
            'name' => 'user_ssh_keys',
            "query" => "SELECT * FROM users CROSS JOIN user_ssh_keys USING (uid);",
            "interval" => 86400,
            "description" => "Returns the private keys in the users ~/.ssh directory and whether or not they are encrypted."
        ], [
            'name' => "yum_sources",
            "query" => "SELECT name, baseurl, enabled, gpgcheck FROM yum_sources;",
            "interval" => 86400,
            "description" => "Display yum package manager sources",
            "snapshot" => true,
            "platform" => "centos"
        ]];
    }

    private function hardwareMonitoringRules(): array
    {
        // See https://github.com/osquery/osquery/blob/master/packs/hardware-monitoring.conf for details
        $rules = <<< EOF
{
  "queries": {
    "acpi_tables": {
      "query": "select * from acpi_tables;",
      "interval": 86400,
      "platform": "posix",
      "version": "1.3.0",
      "description": "General reporting and heuristics monitoring."
    },
    "cpuid": {
      "query": "select feature, value, output_register, output_bit, input_eax from cpuid;",
      "interval": 86400,
      "version": "1.0.4",
      "description": "General reporting and heuristics monitoring."
    },
    "smbios_tables": {
      "query": "select * from smbios_tables;",
      "interval": 86400,
      "platform": "posix",
      "version": "1.3.0",
      "description": "General reporting and heuristics monitoring."
    },
    "nvram": {
      "query": "select * from nvram where name not in ('backlight-level', 'SystemAudioVolumeDB', 'SystemAudioVolume');",
      "interval": 7200,
      "platform": "darwin",
      "version": "1.0.2",
      "description": "Report on crashes, alternate boots, and boot arguments."
    },
    "kernel_info": {
      "query": "select * from kernel_info join hash using (path);",
      "interval": 7200,
      "version": "1.4.0",
      "description": "Report the booted kernel, potential arguments, and the device."
    },
    "pci_devices": {
      "query": "select * from pci_devices;",
      "interval": 7200,
      "platform": "posix",
      "version": "1.0.4",
      "description": "Report an inventory of PCI devices. Attaches and detaches will show up in hardware_events."
    },
    "fan_speeds": {
      "query": "select * from fan_speed_sensors;",
      "interval": 7200,
      "platform": "darwin",
      "version": "1.7.1",
      "description": "Report current fan speeds in the target OSX system."
    },
    "temperatures": {
      "query": "select * from temperature_sensors;",
      "interval": 7200,
      "platform": "darwin",
      "version": "1.7.1",
      "description": "Report current machine temperatures in the target OSX system."
    },
    "usb_devices": {
      "query": "select * from usb_devices;",
      "interval": 7200,
      "platform": "posix",
      "version": "1.2.0",
      "description": "Report an inventory of USB devices. Attaches and detaches will show up in hardware_events."
    },
    "hardware_events": {
      "query" : "select * from hardware_events where path <> '' or model <> '';",
      "interval" : 7200,
      "platform": "posix",
      "removed": false,
      "version" : "1.4.5",
      "description" : "Retrieves all the hardware related events in the target OSX system.",
      "value" : "Determine if a third party device was attached to the system."
    },
    "darwin_kernel_system_controls": {
      "query": "select * from system_controls where subsystem = 'kern' and (name like '%boot%' or name like '%secure%' or name like '%single%');",
      "interval": 7200,
      "platform": "darwin",
      "version": "1.4.3",
      "description": "Double check the information reported in kernel_info and report the kernel signature."
    },
    "iokit_devicetree": {
      "query": "select * from iokit_devicetree;",
      "interval": 86400,
      "platform": "darwin",
      "version": "1.3.0",
      "description": "General inventory of IOKit's devices on OS X."
    },
    "efi_file_hashes": {
      "query": "select file.path, uid, gid, mode, 0 as atime, mtime, ctime, md5, sha1, sha256 from (select * from file where path like '/System/Library/CoreServices/%.efi' union select * from file where path like '/System/Library/LaunchDaemons/com.apple%efi%') file join hash using (path);",
      "interval": 7200,
      "removed": false,
      "version": "1.6.1",
      "platform": "darwin",
      "description": "Hash files related to EFI platform updates and EFI bootloaders on primary boot partition. This does not hash bootloaders on the EFI/boot partition."
    },
    "kernel_extensions": {
      "query" : "select * from kernel_extensions;",
      "interval" : "7200",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the information about the current kernel extensions for the target OSX system."
    },
    "kernel_modules": {
      "query" : "select * from kernel_modules;",
      "interval" : "7200",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the information for the current kernel modules in the target Linux system."
    },
    "windows_drivers": {
      "query" : "select * from drivers;",
      "interval" : "7200",
      "platform" : "windows",
      "version" : "2.2.0",
      "description" : "Retrieves all the information for the current windows drivers in the target Windows system."
    },
    "device_nodes": {
      "query": "select file.path, uid, gid, mode, 0 as atime, mtime, ctime, block_size, type from file where directory = '/dev/';",
      "interval": "7200",
      "platform": "posix",
      "version": "1.6.0",
      "description": "Inventory all 'device' nodes in /dev/."
    }  
  }
}
EOF;
        return json_decode($rules, true);
    }

    private function incidentResponseRules(): array
    {
        // See https://github.com/osquery/osquery/blob/master/packs/incident-response.conf for details
        $rules = <<< EOF
{
  "queries": {
    "launchd": {
      "query" : "select * from launchd;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the daemons that will run in the start of the target OSX system.",
      "value" : "Identify malware that uses this persistence mechanism to launch at system boot"
    },
    "startup_items": {
      "query" : "select * from startup_items;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieve all the items that will load when the target OSX system starts.",
      "value" : "Identify malware that uses this persistence mechanism to launch at a given interval"
    },
    "crontab": {
      "query" : "select * from crontab;",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the jobs scheduled in crontab in the target system.",
      "value" : "Identify malware that uses this persistence mechanism to launch at a given interval"
    },
    "loginwindow1": {
      "query" : "select key, subkey, value from plist where path = '/Library/Preferences/com.apple.loginwindow.plist';",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the values for the loginwindow process in the target OSX system.",
      "value" : "Identify malware that uses this persistence mechanism to launch at system boot"
    },
    "loginwindow2": {
      "query" : "select key, subkey, value from plist where path = '/Library/Preferences/loginwindow.plist';",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the values for the loginwindow process in the target OSX system.",
      "value" : "Identify malware that uses this persistence mechanism to launch at system boot"
    },
    "loginwindow3": {
      "query" : "select username, key, subkey, value from plist p, (select * from users where directory like '/Users/%') u where p.path = u.directory || '/Library/Preferences/com.apple.loginwindow.plist';",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the values for the loginwindow process in the target OSX system.",
      "value" : "Identify malware that uses this persistence mechanism to launch at system boot"
    },
    "loginwindow4": {
      "query" : "select username, key, subkey, value from plist p, (select * from users where directory like '/Users/%') u where p.path = u.directory || '/Library/Preferences/loginwindow.plist';",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the values for the loginwindow process in the target OSX system.",
      "value" : "Identify malware that uses this persistence mechanism to launch at system boot"
    },
    "alf": {
      "query" : "select * from alf;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the configuration values for the Application Layer Firewall for OSX.",
      "value" : "Verify firewall settings are as restrictive as you need. Identify unwanted firewall holes made by malware or humans"
    },
    "alf_exceptions": {
      "query" : "select * from alf_exceptions;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the exceptions for the Application Layer Firewall in OSX.",
      "value" : "Verify firewall settings are as restrictive as you need. Identify unwanted firewall holes made by malware or humans"
    },
    "alf_services": {
      "query" : "select * from alf_services;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the services for the Application Layer Firewall in OSX.",
      "value" : "Verify firewall settings are as restrictive as you need. Identify unwanted firewall holes made by malware or humans"
    },
    "alf_explicit_auths": {
      "query" : "select * from alf_explicit_auths;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of processes with explicit authorization for the Application Layer Firewall.",
      "value" : "Verify firewall settings are as restrictive as you need. Identify unwanted firewall holes made by malware or humans"
    },
    "etc_hosts": {
      "query" : "select * from etc_hosts;",
      "interval" : "86400",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the entries in the target system /etc/hosts file.",
      "value" : "Identify network communications that are being redirected. Example: identify if security logging has been disabled"
    },
    "kextstat": {
      "query" : "select * from kernel_extensions;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the information about the current kernel extensions for the target OSX system.",
      "value" : "Identify malware that has a kernel extension component."
    },
    "kernel_modules": {
      "query" : "select * from kernel_modules;",
      "interval" : "3600",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the information for the current kernel modules in the target Linux system.",
      "value" : "Identify malware that has a kernel module component."
    },
    "last": {
      "query" : "select * from last;",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the list of the latest logins with PID, username and timestamp.",
      "value" : "Useful for intrusion detection and incident response. Verify assumptions of what accounts should be accessing what systems and identify machines accessed during a compromise."
    },
    "installed_applications": {
      "query" : "select * from apps;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the currently installed applications in the target OSX system.",
      "value" : "Identify malware, adware, or vulnerable packages that are installed as an application."
    },
    "open_sockets": {
      "query" : "select distinct pid, family, protocol, local_address, local_port, remote_address, remote_port, path from process_open_sockets where path <> '' or remote_address <> '';",
      "interval" : "86400",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the open sockets per process in the target system.",
      "value" : "Identify malware via connections to known bad IP addresses as well as odd local or remote port bindings"
    },
    "open_files": {
      "query" : "select distinct pid, path from process_open_files where path not like '/private/var/folders%' and path not like '/System/Library/%' and path not in ('/dev/null', '/dev/urandom', '/dev/random');",
      "interval" : "86400",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the open files per process in the target system.",
      "value" : "Identify processes accessing sensitive files they shouldn't"
    },
    "logged_in_users": {
      "query" : "select liu.*, p.name, p.cmdline, p.cwd, p.root from logged_in_users liu, processes p where liu.pid = p.pid;",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the list of all the currently logged in users in the target system.",
      "value" : "Useful for intrusion detection and incident response. Verify assumptions of what accounts should be accessing what systems and identify machines accessed during a compromise."
    },
    "ip_forwarding": {
      "query" : "select * from system_controls where oid = '4.30.41.1' union select * from system_controls where oid = '4.2.0.1';",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the current status of IP/IPv6 forwarding.",
      "value" : "Identify if a machine is being used as relay."
    },
    "process_env": {
      "query" : "select * from process_envs;",
      "interval" : "86400",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the environment variables per process in the target system.",
      "value" : "Insight into the process data: Where was it started from, was it preloaded..."
    },
    "mounts": {
      "query" : "select * from mounts;",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the current list of mounted drives in the target system.",
      "value" : "Scope for lateral movement. Potential exfiltration locations. Potential dormant backdoors."
    },
    "nfs_shares": {
      "query" : "select * from nfs_shares;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the current list of Network File System mounted shares.",
      "value" : "Scope for lateral movement. Potential exfiltration locations. Potential dormant backdoors."
    },
    "shell_history": {
      "query" : "select * from users join shell_history using (uid);",
      "interval" : "86400",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the command history, per user, by parsing the shell history files.",
      "value" : "Identify actions taken. Useful for compromised hosts."
    },
    "recent_items": {
      "query" : "select username, key, value from plist p, (select * from users where directory like '/Users/%') u where p.path = u.directory || '/Library/Preferences/com.apple.recentitems.plist';",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of recent items opened in OSX by parsing the plist per user.",
      "value" : "Identify recently accessed items. Useful for compromised hosts."
    },
    "ramdisk": {
      "query" : "select * from block_devices where type = 'Virtual Interface';",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the ramdisk currently mounted in the target system.",
      "value" : "Identify if an attacker is using temporary, memory storage to avoid touching disk for anti-forensics purposes"
    },
    "listening_ports": {
      "query" : "select * from listening_ports;",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the listening ports in the target system.",
      "value" : "Detect if a listening port iis not mapped to a known process. Find backdoors."
    },
    "suid_bin": {
      "query" : "select * from suid_bin;",
      "interval" : "3600",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves all the files in the target system that are setuid enabled.",
      "value" : "Detect backdoor binaries (attacker may drop a copy of /bin/sh). Find potential elevation points / vulnerabilities in the standard build."
    },
    "process_memory": {
      "query" : "select * from process_memory_map;",
      "interval" : "86400",
      "platform" : "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the memory map per process in the target Linux or macOS system.",
      "value" : "Ability to compare with known good. Identify mapped regions corresponding with or containing injected code."
    },
    "arp_cache": {
      "query" : "select * from arp_cache;",
      "interval" : "3600",
      "version" : "1.4.5",
      "description" : "Retrieves the ARP cache values in the target system.",
      "value" : "Determine if MITM in progress."
    },
    "wireless_networks": {
      "query" : "select ssid, network_name, security_type, last_connected, captive_portal, possibly_hidden, roaming, roaming_profile from wifi_networks;",
      "interval" : "3600",
      "platform" : "darwin",
      "version" : "1.6.0",
      "description" : "Retrieves all the remembered wireless network that the target machine has connected to.",
      "value" : "Identifies connections to rogue access points."
    },
    "disk_encryption": {
      "query" : "select * from disk_encryption;",
      "interval" : "86400",
      "platform": "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the current disk encryption status for the target system.",
      "value" : "Identifies a system potentially vulnerable to disk cloning."
    },
    "iptables": {
      "query" : "select * from iptables;",
      "interval" : "3600",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves the current filters and chains per filter in the target system.",
      "value" : "Verify firewall settings are as restrictive as you need. Identify unwanted firewall holes made by malware or humans"
    },
    "app_schemes": {
      "query" : "select * from app_schemes;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.7",
      "description" : "Retrieves the list of application scheme/protocol-based IPC handlers.",
      "value" : "Post-priori hijack detection, detect potential sensitive information leakage."
    },
    "sandboxes": {
      "query" : "select * from sandboxes;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.7",
      "description" : "Lists the application bundle that owns a sandbox label.",
      "value" : "Post-priori hijack detection, detect potential sensitive information leakage."
    }    
  }
}
EOF;
        return json_decode($rules, true);
    }

    private function itComplianceRules(): array
    {
        // See https://github.com/osquery/osquery/blob/master/packs/it-compliance.conf for details
        $rules = <<< EOF
{
  "queries": {
    "osquery_info": {
      "query" : "select * from time, osquery_info;",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves the current version of the running osquery in the target system and where the configuration was loaded from.",
      "value" : "Identify if your infrastructure is running the correct osquery version and which hosts may have drifted"
    },
    "ad_config": {
      "query" : "select * from ad_config;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the Active Directory configuration for the target machine, attached to the domain (requires sudo).",
      "value" : "Helps you debug domain binding / Active Directory issues in your environment."
    },
    "kernel_info": {
      "query" : "select * from kernel_info;",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves information from the current kernel in the target system.",
      "value" : "Identify out of date kernels or version drift across your infrastructure"
    },
    "os_version": {
      "query" : "select * from os_version;",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves information from the Operative System where osquery is currently running.",
      "value" : "Identify out of date operating systems or version drift across your infrastructure"
    },
    "alf": {
      "query" : "select * from alf;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the configuration values for the Application Layer Firewall for OSX.",
      "value" : "Verify firewall settings are as expected"
    },
    "alf_exceptions": {
      "query" : "select * from alf_exceptions;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the exceptions for the Application Layer Firewall in OSX.",
      "value" : "Verify firewall settings are as expected"
    },
    "alf_services": {
      "query" : "select * from alf_services;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the services for the Application Layer Firewall in OSX.",
      "value" : "Verify firewall settings are as expected"
    },
    "alf_explicit_auths": {
      "query" : "select * from alf_explicit_auths;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of processes with explicit authorization for the Application Layer Firewall.",
      "value" : "Verify firewall settings are as expected"
    },
    "mounts": {
      "query" : "select * from mounts;",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves the current list of mounted drives in the target system.",
      "value" : "Verify if mounts are accessible to those who need it"
    },
    "nfs_shares": {
      "query" : "select * from nfs_shares;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the current list of Network File System mounted shares.",
      "value" : "Verify if shares are accessible to those who need it"
    },
    "windows_shared_resources": {
      "query" : "select * from shared_resources;",
      "interval" : "86400",
      "platform" : "windows",
      "version" : "2.0.0",
      "description" : "Retrieves the list of shared resources in the target Windows system.",
      "value" : "General security posture."
    },
    "browser_plugins": {
      "query" : "select * from users join browser_plugins using (uid);",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of C/NPAPI browser plugins in the target system.",
      "value" : "General security posture."
    },
    "safari_extensions": {
      "query" : "select * from users join safari_extensions using (uid);",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of extensions for Safari in the target system.",
      "value" : "General security posture."
    },
    "chrome_extensions": {
      "query" : "select * from users join chrome_extensions using (uid);",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves the list of extensions for Chrome in the target system.",
      "value" : "General security posture."
    },
    "firefox_addons": {
      "query" : "select * from users join firefox_addons using (uid);",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of addons for Firefox in the target system.",
      "value" : "General security posture."
    },
    "homebrew_packages": {
      "query" : "select * from homebrew_packages;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of brew packages installed in the target OSX system.",
      "value" : "General security posture."
    },
    "windows_programs": {
      "query" : "select * from programs;",
      "interval" : "86400",
      "platform" : "windows",
      "version" : "2.0.0",
      "description" : "Retrieves the list of products as they are installed by Windows Installer in the target Windows system.",
      "value" : "General security posture."
    },
    "windows_patches": {
      "query" : "select * from patches;",
      "interval" : "86400",
      "platform" : "windows",
      "version" : "2.2.0",
      "description" : "Retrieves all the information for the current windows drivers in the target Windows system."
    },
    "package_receipts": {
      "query" : "select * from package_receipts;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the PKG related information stored in OSX.",
      "value" : "General security posture."
    },
    "usb_devices": {
      "query" : "select * from usb_devices;",
      "interval" : "86400",
      "platform" : "posix",
      "version" : "1.4.5",
      "description" : "Retrieves the current list of USB devices in the target system.",
      "value" : "General security posture."
    },
    "keychain_items": {
      "query" : "select * from keychain_items;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the items contained in the keychain in the target OSX system.",
      "value" : "General security posture."
    },
    "deb_packages": {
      "query" : "select * from deb_packages;",
      "interval" : "86400",
      "platform" : "ubuntu",
      "version" : "1.4.5",
      "description" : "Retrieves all the installed DEB packages in the target Linux system.",
      "value" : "General security posture."
    },
    "apt_sources": {
      "query" : "select * from apt_sources;",
      "interval" : "86400",
      "platform" : "ubuntu",
      "version" : "1.4.5",
      "description" : "Retrieves all the APT sources to install packages from in the target Linux system.",
      "value" : "General security posture."
    },
    "portage_packages": {
      "query" : "select * from portage_use;",
      "interval" : "86400",
      "platform" : "gentoo",
      "version" : "2.0.0",
      "description" : "Retrieves all the packages installed with portage from the target Linux system.",
      "value" : "General security posture."
    },
    "kernel_modules": {
      "query" : "select * from kernel_modules;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves the current list of loaded kernel modules in the target Linux system.",
      "value" : "General security posture."
    },
    "windows_drivers": {
      "query" : "select * from drivers;",
      "interval" : "86400",
      "platform" : "windows",
      "version" : "2.2.0",
      "description" : "Retrieves all the information for the current windows drivers in the target Windows system."
    },
    "rpm_packages": {
      "query" : "select * from rpm_packages;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the installed RPM packages in the target Linux system.",
      "value" : "General security posture."
    },
    "installed_applications": {
      "query" : "select * from apps;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the currently installed applications in the target OSX system.",
      "value" : "Find currently installed applications and versions of each."
    },
    "disk_encryption": {
      "query" : "select * from disk_encryption;",
      "interval" : "86400",
      "version" : "1.4.5",
      "platform" : "posix",
      "description" : "Retrieves the current disk encryption status for the target system.",
      "value" : "Identifies a system potentially vulnerable to disk cloning."
    },
    "launchd": {
      "query" : "select * from launchd;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the daemons that will run in the start of the target OSX system.",
      "value" : "Visibility on what starts in the system."
    },
    "iptables": {
      "query" : "select * from iptables;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves the current filters and chains per filter in the target system.",
      "value" : "General security posture."
    },
    "sip_config": {
      "query" : "select * from sip_config;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.7.0",
      "description" : "Retrieves the current System Integrity Protection configuration in the target system.",
      "value" : "General security posture."
    }    
  }
}
EOF;
        return json_decode($rules, true);
    }

    private function osqueryMonitoringRules(): array
    {
        // See https://github.com/osquery/osquery/blob/master/packs/osquery-monitoring.conf for details
        $rules = <<< EOF
{
  "queries": {
    "schedule": {
      "query": "select name, interval, executions, output_size, wall_time, (user_time/executions) as avg_user_time, (system_time/executions) as avg_system_time, average_memory, last_executed, denylisted from osquery_schedule;",
      "interval": 7200,
      "removed": false,
      "denylist": false,
      "version": "2.11.0",
      "description": "Report performance for every query within packs and the general schedule."
    },
    "events": {
      "query": "select name, publisher, type, subscriptions, events, active from osquery_events;",
      "interval": 86400,
      "removed": false,
      "denylist": false,
      "version": "1.5.3",
      "description": "Report event publisher health and track event counters."
    },
    "osquery_info": {
      "query": "select i.*, p.resident_size, p.user_time, p.system_time, time.minutes as counter from osquery_info i, processes p, time where p.pid = i.pid;",
      "interval": 600,
      "removed": false,
      "denylist": false,
      "version": "1.2.2",
      "description": "A heartbeat counter that reports general performance (CPU, memory) and version."
    }
  }
}
EOF;
        return json_decode($rules, true);
    }

    private function ossecRootkitRules(): array
    {
        // See https://github.com/osquery/osquery/blob/master/packs/ossec-rootkit.conf for details
        $rules = <<< EOF
{
  "queries": {
   "bash_door": {
      "query": "select * from file where path in ('/tmp/mcliZokhb', '/tmp/mclzaKmfa');", 
      "interval": "3600", 
      "description": "bash_door", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "slapper_installed": {
      "query": "select * from file where path in ('/tmp/.bugtraq', '/tmp/.bugtraq.c', '/tmp/.cinik', '/tmp/.b', '/tmp/httpd', '/tmp./update', '/tmp/.unlock', '/tmp/.font-unix/.cinik', '/tmp/.cinik');", 
      "interval": "3600", 
      "description": "slapper_installed", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "mithra`s_rootkit": {
      "query": "select * from file where path in ('/usr/lib/locale/uboot');", 
      "interval": "3600", 
      "description": "mithra`s_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "omega_worm": {
      "query": "select * from file where path in ('/dev/chr');", 
      "interval": "3600", 
      "description": "omega_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "kenga3_rootkit": {
      "query": "select * from file where path in ('/usr/include/. .');", 
      "interval": "3600", 
      "description": "kenga3_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "sadmind/iis_worm": {
      "query": "select * from file where path in ('/dev/cuc');", 
      "interval": "3600", 
      "description": "sadmind/iis_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "rsha": {
      "query": "select * from file where path in ('/usr/bin/kr4p', '/usr/bin/n3tstat', '/usr/bin/chsh2', '/usr/bin/slice2', '/etc/rc.d/rsha');", 
      "interval": "3600", 
      "description": "rsha", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "old_rootkits": {
      "query": "select * from file where path in ('/usr/include/rpc/ ../kit', '/usr/include/rpc/ ../kit2', '/usr/doc/.sl', '/usr/doc/.sp', '/usr/doc/.statnet', '/usr/doc/.logdsys', '/usr/doc/.dpct', '/usr/doc/.gifnocfi', '/usr/doc/.dnif', '/usr/doc/.nigol');", 
      "interval": "3600", 
      "description": "old_rootkits", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "telekit_trojan": {
      "query": "select * from file where path in ('/dev/hda06', '/usr/info/libc1.so');", 
      "interval": "3600", 
      "description": "telekit_trojan", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "tc2_worm": {
      "query": "select * from file where path in ('/usr/info/.tc2k', '/usr/bin/util', '/usr/sbin/initcheck', '/usr/sbin/ldb');", 
      "interval": "3600", 
      "description": "tc2_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "shitc": {
      "query": "select * from file where path in ('/bin/home', '/sbin/home', '/usr/sbin/in.slogind');", 
      "interval": "3600", 
      "description": "shitc", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "rh_sharpe": {
      "query": "select * from file where path in ('/bin/.ps', '/usr/bin/cleaner', '/usr/bin/slice', '/usr/bin/vadim', '/usr/bin/.ps', '/bin/.lpstree', '/usr/bin/.lpstree', '/usr/bin/lnetstat', '/bin/lnetstat', '/usr/bin/ldu', '/bin/ldu', '/usr/bin/lkillall', '/bin/lkillall', '/usr/include/rpcsvc/du');", 
      "interval": "3600", 
      "description": "rh_sharpe", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "showtee_/_romanian_rootkit": {
      "query": "select * from file where path in ('/usr/include/addr.h', '/usr/include/file.h', '/usr/include/syslogs.h', '/usr/include/proc.h');", 
      "interval": "3600", 
      "description": "showtee_/_romanian_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "lrk_rootkit": {
      "query": "select * from file where path in ('/dev/ida/.inet');", 
      "interval": "3600", 
      "description": "lrk_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "zk_rootkit": {
      "query": "select * from file where path in ('/usr/share/.zk', '/usr/share/.zk/zk', '/etc/1ssue.net', '/usr/X11R6/.zk', '/usr/X11R6/.zk/xfs', '/usr/X11R6/.zk/echo', '/etc/sysconfig/console/load.zk');", 
      "interval": "3600", 
      "description": "zk_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "ramen_worm": {
      "query": "select * from file where path in ('/usr/lib/ldlibps.so', '/usr/lib/ldlibns.so', '/usr/lib/ldliblogin.so', '/usr/src/.poop', '/tmp/ramen.tgz', '/etc/xinetd.d/asp');", 
      "interval": "3600", 
      "description": "ramen_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "maniac_rk": {
      "query": "select * from file where path in ('/usr/bin/mailrc');", 
      "interval": "3600", 
      "description": "maniac_rk", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "bmbl_rootkit": {
      "query": "select * from file where path in ('/etc/.bmbl', '/etc/.bmbl/sk');", 
      "interval": "3600", 
      "description": "bmbl_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "suckit_rootkit": {
      "query": "select * from file where path in ('/lib/.x', '/lib/sk');", 
      "interval": "3600", 
      "description": "suckit_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "adore_rootkit": {
      "query": "select * from file where path in ('/etc/bin/ava', '/etc/sbin/ava');", 
      "interval": "3600", 
      "description": "adore_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "ldp_worm": {
      "query": "select * from file where path in ('/dev/.kork', '/bin/.login', '/bin/.ps');", 
      "interval": "3600", 
      "description": "ldp_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "romanian_rootkit": {
      "query": "select * from file where path in ('/usr/sbin/initdl', '/usr/sbin/xntps');", 
      "interval": "3600", 
      "description": "romanian_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "illogic_rootkit": {
      "query": "select * from file where path in ('/lib/security/.config', '/usr/bin/sia', '/etc/ld.so.hash');", 
      "interval": "3600", 
      "description": "illogic_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "bobkit_rootkit": {
      "query": "select * from file where path in ('/usr/include/.../', '/usr/lib/.../', '/usr/sbin/.../', '/usr/bin/ntpsx', '/tmp/.bkp', '/usr/lib/.bkit-');", 
      "interval": "3600", 
      "description": "bobkit_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "monkit": {
      "query": "select * from file where path in ('/lib/defs');", 
      "interval": "3600", 
      "description": "monkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "override_rootkit": {
      "query": "select * from file where path in ('/dev/grid-hide-pid-', '/dev/grid-unhide-pid-', '/dev/grid-show-pids', '/dev/grid-hide-port-', '/dev/grid-unhide-port-');", 
      "interval": "3600", 
      "description": "override_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "madalin_rootkit": {
      "query": "select * from file where path in ('/usr/include/icekey.h', '/usr/include/iceconf.h', '/usr/include/iceseed.h');", 
      "interval": "3600", 
      "description": "madalin_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "solaris_worm": {
      "query": "select * from file where path in ('/var/adm/.profile', '/var/spool/lp/.profile', '/var/adm/sa/.adm', '/var/spool/lp/admins/.lp');", 
      "interval": "3600", 
      "description": "solaris_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "phalanx_rootkit": {
      "query": "select * from file where path in ('/usr/share/.home*', '/usr/share/.home*/tty', '/etc/host.ph1', '/bin/host.ph1');", 
      "interval": "3600", 
      "description": "phalanx_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "ark_rootkit": {
      "query": "select * from file where path in ('/dev/ptyxx');", 
      "interval": "3600", 
      "description": "ark_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "tribe_bot": {
      "query": "select * from file where path in ('/dev/wd4');", 
      "interval": "3600", 
      "description": "tribe_bot", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "cback_worm": {
      "query": "select * from file where path in ('/tmp/cback', '/tmp/derfiq');", 
      "interval": "3600", 
      "description": "cback_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "optickit": {
      "query": "select * from file where path in ('/usr/bin/xchk', '/usr/bin/xsf', '/usr/bin/xsf', '/usr/bin/xchk');", 
      "interval": "3600", 
      "description": "optickit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "anonoiyng_rootkit": {
      "query": "select * from file where path in ('/usr/sbin/mech', '/usr/sbin/kswapd');", 
      "interval": "3600", 
      "description": "anonoiyng_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "loc_rookit": {
      "query": "select * from file where path in ('/tmp/xp', '/tmp/kidd0.c', '/tmp/kidd0');", 
      "interval": "3600", 
      "description": "loc_rookit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "showtee": {
      "query": "select * from file where path in ('/usr/lib/.egcs', '/usr/lib/.wormie', '/usr/lib/.kinetic', '/usr/lib/liblog.o', '/usr/include/cron.h', '/usr/include/chk.h');", 
      "interval": "3600", 
      "description": "showtee", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "zarwt_rootkit": {
      "query": "select * from file where path in ('/bin/imin', '/bin/imout');", 
      "interval": "3600", 
      "description": "zarwt_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "lion_worm": {
      "query": "select * from file where path in ('/dev/.lib', '/dev/.lib/1iOn.sh', '/bin/mjy', '/bin/in.telnetd', '/usr/info/torn');", 
      "interval": "3600", 
      "description": "lion_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "suspicious_file": {
      "query": "select * from file where path in ('/etc/rc.d/init.d/rc.modules', '/lib/ldd.so', '/usr/man/muie', '/usr/X11R6/include/pain', '/usr/bin/sourcemask', '/usr/bin/ras2xm', '/usr/bin/ddc', '/usr/bin/jdc', '/usr/sbin/in.telnet', '/sbin/vobiscum', '/usr/sbin/jcd', '/usr/sbin/atd2', '/usr/bin/ishit', '/usr/bin/.etc', '/usr/bin/xstat', '/var/run/.tmp', '/usr/man/man1/lib/.lib', '/usr/man/man2/.man8', '/var/run/.pid', '/lib/.so', '/lib/.fx', '/lib/lblip.tk', '/usr/lib/.fx', '/var/local/.lpd', '/dev/rd/cdb', '/dev/.rd/', '/usr/lib/pt07', '/usr/bin/atm', '/tmp/.cheese', '/dev/.arctic', '/dev/.xman', '/dev/.golf', '/dev/srd0', '/dev/ptyzx', '/dev/ptyzg', '/dev/xdf1', '/dev/ttyop', '/dev/ttyof', '/dev/hd7', '/dev/hdx1', '/dev/hdx2', '/dev/xdf2', '/dev/ptyp', '/dev/ptyr', '/sbin/pback', '/usr/man/man3/psid', '/proc/kset', '/usr/bin/gib', '/usr/bin/snick', '/usr/bin/kfl', '/tmp/.dump', '/var/.x', '/var/.x/psotnic');", 
      "interval": "3600", 
      "description": "suspicious_file", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "apa_kit": {
      "query": "select * from file where path in ('/usr/share/.aPa');", 
      "interval": "3600", 
      "description": "apa_kit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "enye_sec_rootkit": {
      "query": "select * from file where path in ('/etc/.enyelkmHIDE^IT.ko');", 
      "interval": "3600", 
      "description": "enye_sec_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "rk17": {
      "query": "select * from file where path in ('/bin/rtty', '/bin/squit', '/sbin/pback', '/proc/kset', '/usr/src/linux/modules/autod.o', '/usr/src/linux/modules/soundx.o');", 
      "interval": "3600", 
      "description": "rk17", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "trk_rootkit": {
      "query": "select * from file where path in ('/usr/bin/soucemask', '/usr/bin/sourcemask');", 
      "interval": "3600", 
      "description": "trk_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "scalper_installed": {
      "query": "select * from file where path in ('/tmp/.uua', '/tmp/.a');", 
      "interval": "3600", 
      "description": "scalper_installed", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "hidr00tkit": {
      "query": "select * from file where path in ('/var/lib/games/.k');", 
      "interval": "3600", 
      "description": "hidr00tkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "beastkit_rootkit": {
      "query": "select * from file where path in ('/usr/local/bin/bin', '/usr/man/.man10', '/usr/sbin/arobia', '/usr/lib/elm/arobia', '/usr/local/bin/.../bktd');", 
      "interval": "3600", 
      "description": "beastkit_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "shv5_rootkit": {
      "query": "select * from file where path in ('/lib/libsh.so', '/usr/lib/libsh');", 
      "interval": "3600", 
      "description": "shv5_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "esrk_rootkit": {
      "query": "select * from file where path in ('/usr/lib/tcl5.3');", 
      "interval": "3600", 
      "description": "esrk_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "shkit_rootkit": {
      "query": "select * from file where path in ('/lib/security/.config', '/etc/ld.so.hash');", 
      "interval": "3600", 
      "description": "shkit_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "knark_installed": {
      "query": "select * from file where path in ('/proc/knark', '/dev/.pizda', '/dev/.pula', '/dev/.pula');", 
      "interval": "3600", 
      "description": "knark_installed", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "volc_rootkit": {
      "query": "select * from file where path in ('/usr/lib/volc', '/usr/bin/volc');", 
      "interval": "3600", 
      "description": "volc_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "fu_rootkit": {
      "query": "select * from file where path in ('/sbin/xc', '/usr/include/ivtype.h', '/bin/.lib');", 
      "interval": "3600", 
      "description": "fu_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "ajakit_rootkit": {
      "query": "select * from file where path in ('/lib/.ligh.gh', '/lib/.libgh.gh', '/lib/.libgh-gh', '/dev/tux', '/dev/tux/.proc', '/dev/tux/.file');", 
      "interval": "3600", 
      "description": "ajakit_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "monkit_found": {
      "query": "select * from file where path in ('/usr/lib/libpikapp.a');", 
      "interval": "3600", 
      "description": "monkit_found", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "t0rn_rootkit": {
      "query": "select * from file where path in ('/usr/src/.puta', '/usr/info/.t0rn', '/lib/ldlib.tk', '/etc/ttyhash', '/sbin/xlogin');", 
      "interval": "3600", 
      "description": "t0rn_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "adore_worm": {
      "query": "select * from file where path in ('/dev/.shit/red.tgz', '/usr/lib/libt', '/usr/bin/adore');", 
      "interval": "3600", 
      "description": "adore_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "55808.a_worm": {
      "query": "select * from file where path in ('/tmp/.../a', '/tmp/.../r');", 
      "interval": "3600", 
      "description": "55808.a_worm", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }, 
    "tuxkit_rootkit": {
      "query": "select * from file where path in ('/dev/tux', '/usr/bin/xsf', '/usr/bin/xchk');", 
      "interval": "3600", 
      "description": "tuxkit_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    },
    "reptile_rootkit": {
      "query": "select * from file where path in ('/reptile/reptile_cmd', '/lib/udev/reptile');", 
      "interval": "3600", 
      "description": "reptile_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    },
    "beurk_rootkit": {
      "query": "select * from file where path in ('/lib/libselinux.so');", 
      "interval": "3600", 
      "description": "beurk_rootkit", 
      "value": "Artifacts used by this malware", 
      "platform": "linux"
    }
  }
}
EOF;
        return json_decode($rules, true);
    }

    private function vulnManagementRules(): array
    {
        // See https://github.com/osquery/osquery/blob/master/packs/vuln-management.conf for details
        $rules = <<< EOF
{
  "queries": {
    "kernel_info": {
      "query" : "select * from kernel_info;",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves information from the current kernel in the target system.",
      "value" : "Kernel version can tell you vulnerabilities based on the version"
    },
    "os_version": {
      "query" : "select * from os_version;",
      "interval" : "86400",
      "version" : "1.4.5",
      "description" : "Retrieves the current version of the running osquery in the target system and where the configuration was loaded from.",
      "value" : "OS version will tell which distribution the OS is running on, allowing to detect the main distribution"
    },
    "kextstat": {
      "query" : "select * from kernel_extensions;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the information about the current kernel extensions for the target OSX system.",
      "value" : "Only for OS X.  It may pinpoint inserted modules that can carry malicious payloads."
    },
    "kernel_modules": {
      "query" : "select * from kernel_modules;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the information for the current kernel modules in the target Linux system.",
      "value" : "Only for Linux.  It may pinpoint inserted modules that can carry malicious payloads."
    },
    "installed_applications": {
      "query" : "select * from apps;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the currently installed applications in the target OSX system.",
      "value" : "This, with the help of a vulnerability feed, can help tell if a vulnerable application is installed."
    },
    "browser_plugins": {
      "query" : "select browser_plugins.* from users join browser_plugins using (uid);",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.6.1",
      "description" : "Retrieves the list of C/NPAPI browser plugins in the target system.",
      "value" : "General security posture."
    },
    "safari_extensions": {
      "query" : "select safari_extensions.* from users join safari_extensions using (uid);",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.6.1",
      "description" : "Retrieves the list of extensions for Safari in the target system.",
      "value" : "General security posture."
    },
    "opera_extensions": {
      "query" : "select opera_extensions.* from users join opera_extensions using (uid);",
      "interval" : "86400",
      "platform" : "posix",
      "version" : "1.6.1",
      "description" : "Retrieves the list of extensions for Opera in the target system.",
      "value" : "General security posture."
    },
    "chrome_extensions": {
      "query" : "select chrome_extensions.* from users join chrome_extensions using (uid);",
      "interval" : "86400",
      "version" : "1.6.1",
      "description" : "Retrieves the list of extensions for Chrome in the target system.",
      "value" : "General security posture."
    },
    "firefox_addons": {
      "query" : "select firefox_addons.* from users join firefox_addons using (uid);",
      "interval" : "86400",
      "platform" : "posix",
      "version" : "1.6.1",
      "description" : "Retrieves the list of addons for Firefox in the target system.",
      "value" : "General security posture."
    },
    "homebrew_packages": {
      "query" : "select * from homebrew_packages;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves the list of brew packages installed in the target OSX system.",
      "value" : "This, with the help of a vulnerability feed, can help tell if a vulnerable application is installed."
    },
    "package_receipts": {
      "query" : "select * from package_receipts;",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all the PKG related information stored in OSX.",
      "value" : "It could give you a trail of installed/deleted packages"
    },
    "deb_packages": {
      "query" : "select * from deb_packages;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the installed DEB packages in the target Linux system.",
      "value" : "This, with the help of vulnerability feed, can help tell if a vulnerable application is installed."
    },
    "apt_sources": {
      "query" : "select * from apt_sources;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the APT sources to install packages from in the target Linux system.",
      "value" : "In the future this may not have a lot of value as we expect to have installed only signed packages"
    },
    "portage_packages": {
      "query" : "select * from portage_packages;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "2.0.0",
      "description" : "Retrieves all the installed packages on the target Linux system.",
      "value" : "This, with the help of vulnerability feed, can help tell if a vulnerable application is installed."
    },
    "rpm_packages": {
      "query" : "select * from rpm_packages;",
      "interval" : "86400",
      "platform" : "linux",
      "version" : "1.4.5",
      "description" : "Retrieves all the installed RPM packages in the target Linux system.",
      "value" : "This, with the help of vulnerability feed, can help tell if a vulnerable application is installed."
    },
    "unauthenticated_sparkle_feeds": {
      "query" : "select feeds.*, p2.value as sparkle_version from (select a.name as app_name, a.path as app_path, a.bundle_identifier as bundle_id, p.value as feed_url from (select name, path, bundle_identifier from apps) a, plist p where p.path = a.path || '/Contents/Info.plist' and p.key = 'SUFeedURL' and feed_url like 'http://%') feeds left outer join plist p2 on p2.path = app_path || '/Contents/Frameworks/Sparkle.framework/Resources/Info.plist' where (p2.key = 'CFBundleShortVersionString' OR coalesce(p2.key, '') = '');",
      "interval" : "86400",
      "platform" : "darwin",
      "version" : "1.4.5",
      "description" : "Retrieves all application bundles using unauthenticated Sparkle update feeds. See (https://vulnsec.com/2016/osx-apps-vulnerabilities/) for details.",
      "value" : "Tracking vulnerable applications updates may allow blocking of DNS or removal by BundleID."
    },
    "backdoored_python_packages": {
      "query" : "select name as package_name, version as package_version, path as package_path from python_packages where package_name = 'acqusition' or package_name = 'apidev-coop' or package_name = 'bzip' or package_name = 'crypt' or package_name = 'django-server' or package_name = 'pwd' or package_name = 'setup-tools' or package_name = 'telnet' or package_name = 'urlib3' or package_name = 'urllib';",
      "interval" : "86400",
      "platform" : "posix",
      "version" : "1.4.5",
      "description" : "Watches for the backdoored Python packages installed on system. See (http://www.nbu.gov.sk/skcsirt-sa-20170909-pypi/index.html)",
      "value" : "Gives some assurances that no bad Python packages are installed on the system."
    }      
  }
}
EOF;
        return json_decode($rules, true);
    }
}
