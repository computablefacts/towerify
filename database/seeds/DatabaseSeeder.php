<?php

use App\Hashing\TwHasher;
use App\Helpers\AppStore;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Property;
use App\Models\Role;
use App\Models\TaxCategory;
use App\Models\Taxon;
use App\Models\Taxonomy;
use App\Models\TaxRate;
use App\Models\Zone;
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
        Permission::where('name', 'configure ssh connections')->delete();
        Permission::where('name', 'configure app permissions')->delete();
        Permission::where('name', 'configure user apps')->delete();
        Permission::where('name', 'deploy apps')->delete();
        Permission::where('name', 'launch apps')->delete();
        Permission::where('name', 'send invitations')->delete();

        // Create missing permissions
        foreach (Role::ROLES as $role => $permissions) {
            foreach ($permissions as $permission) {
                $perm = Permission::firstOrCreate(
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
        foreach (Role::ROLES as $role => $permissions) {
            $role = Role::firstOrcreate([
                'name' => $role
            ]);
            foreach ($permissions as $permission) {
                $perm = Permission::where('name', $permission)->firstOrFail();
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
                'password' => TwHasher::hash($password),
                'type' => 'admin',
                'is_active' => true,
            ]
        );

        // Add the 'admin' role to the user
        $admin = Role::where('name', Role::ADMIN)->first();

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
        $zoneIsEu = Zone::firstOrCreate(['name' => 'EU']);
        $zoneIsEu->scope = ZoneScope::TAXATION();
        \Konekt\Address\Models\Country::where('is_eu_member', true)
            ->get()
            ->each(function (\Konekt\Address\Models\Country $country) use ($zoneIsEu) {
                $zoneIsEu->addCountry($country);
            });
        $zoneIsEu->save();

        // Create tax category
        $taxCategoryIsVat = TaxCategory::firstOrCreate(
            ['name' => 'VAT'],
            [
                'is_active' => true,
            ]
        );

        // Create tax rate
        $taxRateForEuVat = TaxRate::firstOrCreate(
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

    // See https://vanilo.io/docs/4.x/categorization for details
    private function setupProductCategories(): void
    {
        // Remove support for legacy taxonomies and taxons
        Taxonomy::where('name', Taxonomy::APPLICATIONS)->delete();
        Taxonomy::where('name', Taxonomy::APPLICATIONS)->delete();
        Taxonomy::where('name', Taxonomy::SERVERS)->delete();
        Taxonomy::where('name', Taxonomy::SERVERS)->delete();
        Taxonomy::where('name', Taxon::SUBSCRIPTIONS)->delete();
        Taxonomy::where('name', Taxon::YUNOHOST)->delete();
        Taxon::where('name', 'Baremetal')->delete();

        // Create the 'Root' product category
        /** @var \App\Models\Taxonomy $all */
        $root = Taxonomy::firstOrCreate(['name' => Taxonomy::ROOT]);

        // Under the 'Root' product category, create two entries : 'Subscriptions' and 'YunoHost'
        $subscriptions = Taxon::firstOrCreate(
            ['name' => Taxon::SUBSCRIPTIONS],
            ['name' => Taxon::SUBSCRIPTIONS, 'taxonomy_id' => $root->id]
        );

        $yunohost = Taxon::firstOrCreate(
            ['name' => Taxon::YUNOHOST],
            ['name' => Taxon::YUNOHOST, 'taxonomy_id' => $root->id]
        );

        // Under the 'Subscriptions' subcategory, create two entries : 'Yearly' and 'Monthly'
        $yearly = Taxon::firstOrCreate(
            ['name' => Taxon::YEARLY],
            ['name' => Taxon::YEARLY, 'taxonomy_id' => $root->id, 'parent_id' => $subscriptions->id]
        );

        $monthly = Taxon::firstOrCreate(
            ['name' => Taxon::MONTHLY],
            ['name' => Taxon::MONTHLY, 'taxonomy_id' => $root->id, 'parent_id' => $subscriptions->id]
        );

        // Under the 'YunoHost' subcategory, import categories from the AppStore
        $priority = 1;

        foreach (AppStore::categories() as $category) {
            $taxon = Taxon::updateOrCreate(
                ['name' => $category],
                [
                    'taxonomy_id' => $root->id,
                    'parent_id' => $yunohost->id,
                    'name' => $category,
                    'priority' => $priority++,
                ]
            );
        }
    }

    /** @deprecated */
    private function setupProductProperties(): void
    {
        // Remove support for legacy properties
        Property::where('slug', Property::RAM_SLUG)->delete();
        Property::where('slug', Property::CPU_SLUG)->delete();
        Property::where('slug', Property::STORAGE_SLUG)->delete();
    }

    private function setupProducts(): void
    {
        // Load tax rate
        $taxCategory = TaxCategory::findByName('VAT');

        // Load apps from the AppStore
        foreach (AppStore::catalog() as $app) {

            $product = Product::updateOrCreate(
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

            $taxon = Taxon::where('name', $app['category'])
                ->get()
                ->firstOrFail();

            $product->taxons()->syncWithoutDetaching($taxon);
        }
    }

    private function setupOsqueryRules(): void
    {
        $rules = $this->mitreAttckRules();
        foreach ($rules as $rule) {
            $this->addOrUpdateOsqueryRule($rule['name'], 'mitre att&ck', $rule);
        }

        $rules = $this->metricsRules();
        foreach ($rules as $rule) {
            $this->addOrUpdateOsqueryRule($rule['name'], 'metrics', $rule);
        }

        $rules = $this->securityRules();
        foreach ($rules as $rule) {
            $this->addOrUpdateOsqueryRule($rule['name'], isset($rule['attck']) ? 'mitre att&ck' : 'security', $rule);
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
        if (isset($rule['enabled'])) {
            $fields['enabled'] = $rule['enabled'];
        }
        if (isset($rule['attck'])) {
            $fields['attck'] = $rule['attck'];
        }
        \App\Models\YnhOsqueryRule::updateOrCreate(['name' => $name], $fields);
    }

    private function metricsRules(): array
    {
        return [[
            'name' => 'processor_available_snapshot',
            'query' => "SELECT printf(ROUND((CAST(SUM(system) AS FLOAT)/(SUM(idle)+SUM(system)+SUM(USER)))*100,2)) AS time_spent_on_system_workloads_pct, printf(ROUND((CAST(SUM(USER) AS FLOAT)/(SUM(idle)+SUM(system)+SUM(USER)))*100,2)) AS time_spent_on_user_workloads_pct, printf(ROUND((CAST(SUM(idle) AS FLOAT)/(SUM(idle)+SUM(system)+SUM(USER)))*100,2)) AS time_spent_idle_pct FROM cpu_time;",
            'description' => 'Track processor usage.',
            'interval' => 300,
            'snapshot' => true,
            'enabled' => true,
        ], [
            'name' => "memory_available_snapshot",
            'query' => "SELECT printf('%.2f',((memory_total - memory_available) * 1.0)/1073741824) AS used_space_gb, printf('%.2f',(1.0 * memory_available / 1073741824)) AS space_left_gb, printf('%.2f',(1.0 * memory_total / 1073741824)) AS total_space_gb, printf('%.2f',(((memory_total - memory_available) * 1.0)/1073741824)/(1.0 * memory_total / 1073741824)) * 100 AS '%_used', printf('%.2f',(1.0 * memory_available / 1073741824)/(1.0 * memory_total / 1073741824)) * 100 AS '%_available' FROM memory_info;",
            'description' => "Track memory usage.",
            'interval' => 300,
            'snapshot' => true,
            'enabled' => true,
        ], [
            'name' => "disk_available_snapshot",
            'query' => "SELECT printf('%.2f',((blocks - blocks_available * 1.0) * blocks_size)/1073741824) AS used_space_gb, printf('%.2f',(1.0 * blocks_available * blocks_size / 1073741824)) AS space_left_gb, printf('%.2f',(1.0 * blocks * blocks_size / 1073741824)) AS total_space_gb, printf('%.2f',(((blocks - blocks_available * 1.0) * blocks_size)/1073741824)/(1.0 * blocks * blocks_size / 1073741824)) * 100 AS '%_used', printf('%.2f',(1.0 * blocks_available * blocks_size / 1073741824)/(1.0 * blocks * blocks_size / 1073741824)) * 100 AS '%_available' FROM mounts WHERE path = '/';",
            'description' => "Track disk usage.",
            'interval' => 300,
            'snapshot' => true,
            'enabled' => true,
        ]];
    }

    private function securityRules(): array
    {
        // Sources :
        // - https://github.com/osquery/osquery/blob/master/packs/hardware-monitoring.conf
        // - https://github.com/osquery/osquery/blob/master/packs/incident-response.conf
        // - https://github.com/osquery/osquery/blob/master/packs/it-compliance.conf
        // - https://github.com/osquery/osquery/blob/master/packs/osquery-monitoring.conf
        // - https://github.com/osquery/osquery/blob/master/packs/ossec-rootkit.conf
        // - https://github.com/osquery/osquery/blob/master/packs/vuln-management.conf
        return [[
            'name' => "authorized_keys",
            'query' => "SELECT * FROM users CROSS JOIN authorized_keys USING (uid);",
            'description' => "Retrieves the list of authorized_keys for each user.",
            'interval' => 3600,
            'enabled' => true,
        ], [
            'name' => 'users',
            "query" => "SELECT * FROM users;",
            "interval" => 3600,
            "description" => "Retrieves the list of local system users.",
            'enabled' => true,
        ], [
            "name" => "last",
            "query" => "SELECT * FROM last;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the list of the latest logins with PID, username and timestamp.",
            "value" => "Useful for intrusion detection and incident response. Verify assumptions of what accounts should be accessing what systems and identify machines accessed during a compromise.",
            "attck" => "T1136,T1078",
            "enabled" => true,
        ], [
            "name" => 'suid_bin',
            "query" => "SELECT * FROM suid_bin;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the files in the target system that are setuid enabled.",
            "value" => "Detect backdoor binaries (attacker may drop a copy of /bin/sh). Find potential elevation points / vulnerabilities in the standard build.",
            "enabled" => true,
        ], [
            'name' => 'ld_preload',
            "query" => "SELECT process_envs.pid, process_envs.key, process_envs.value, processes.name, processes.path, processes.cmdline, processes.cwd FROM process_envs JOIN processes USING (pid) WHERE KEY = 'LD_PRELOAD';",
            "interval" => 60,
            "platform" => "linux",
            "description" => "Any processes that run with an LD_PRELOAD environment variable.",
            "snapshot" => true,
            "enabled" => true,
        ], [
            "name" => 'kernel_modules',
            "query" => "SELECT * FROM kernel_modules;",
            "interval" => 3600,
            "platform" => "linux",
            "version" => "1.4.5",
            "description" => "Retrieves all the information for the current kernel modules in the target Linux system.",
            "value" => "Identify malware that has a kernel module component.",
            "enabled" => true,
        ], [
            'name' => 'crontab',
            "query" => "SELECT * FROM crontab;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the jobs scheduled in crontab in the target system.",
            "value" => "Identify malware that uses this persistence mechanism to launch at a given interval",
            "enabled" => true
        ], [
            "name" => "etc_hosts",
            "query" => "SELECT * FROM etc_hosts;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the entries in the target system /etc/hosts file.",
            "value" => "Identify network communications that are being redirected. Example: identify if security logging has been disabled",
            "enabled" => true,
        ], [
            'name' => "shell_history",
            "query" => "SELECT * FROM users JOIN shell_history USING (uid);",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the command history, per user, by parsing the shell history files.",
            "value" => "Identify actions taken. Useful for compromised hosts.",
            "enabled" => true,
        ], [
            'name' => "logged_in_users",
            "query" => "SELECT liu.*, p.name, p.cmdline, p.cwd, p.root FROM logged_in_users liu, processes p WHERE liu.pid = p.pid;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the list of all the currently logged in users in the target system.",
            "value" => "Useful for intrusion detection and incident response. Verify assumptions of what accounts should be accessing what systems and identify machines accessed during a compromise.",
            "attck" => "T1136,T1078,T1169,T1184,T1021",
            "enabled" => true,
        ], [
            'name' => "ip_forwarding",
            "query" => "SELECT * FROM system_controls WHERE oid = '4.30.41.1' UNION SELECT * FROM system_controls WHERE oid = '4.2.0.1';",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the current status of IP/IPv6 forwarding.",
            "value" => "Identify if a machine is being used as relay.",
            "enabled" => true,
        ], [
            'name' => "listening_ports",
            "query" => "SELECT * FROM listening_ports;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the listening ports in the target system.",
            "value" => "Detect if a listening port is not mapped to a known process. Find backdoors.",
            "enabled" => true,
        ], [
            'name' => "wireless_networks",
            "query" => "SELECT ssid, network_name, security_type, last_connected, captive_portal, possibly_hidden, roaming, roaming_profile FROM wifi_networks;",
            "interval" => 3600,
            "platform" => "darwin",
            "version" => "1.6.0",
            "description" => "Retrieves all the remembered wireless network that the target machine has connected to.",
            "value" => "Identifies connections to rogue access points.",
            "enabled" => true,
        ], [
            'name' => "open_sockets",
            "query" => "SELECT DISTINCT pid, family, protocol, local_address, local_port, remote_address, remote_port, path FROM process_open_sockets WHERE path <> '' OR remote_address <> '';",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the open sockets per process in the target system.",
            "value" => "Identify malware via connections to known bad IP addresses as well as odd local or remote port bindings",
            "enabled" => true,
        ], [
            'name' => "open_files",
            "query" => "SELECT DISTINCT pid, path FROM process_open_files WHERE path NOT LIKE '/private/var/folders%' AND path NOT LIKE '/System/Library/%' AND path NOT IN ('/dev/null', '/dev/urandom', '/dev/random');",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the open files per process in the target system.",
            "value" => "Identify processes accessing sensitive files they shouldn't",
            "enabled" => true,
        ], [
            'name' => "process_env",
            "query" => "SELECT * FROM process_envs;",
            "interval" => "86400",
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the environment variables per process in the target system.",
            "value" => "Insight into the process data: Where was it started from, was it preloaded...",
            "enabled" => true,
        ], [
            'name' => "ramdisk",
            "query" => "SELECT * FROM block_devices WHERE type = 'Virtual Interface';",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the ramdisk currently mounted in the target system.",
            "value" => "Identify if an attacker is using temporary, memory storage to avoid touching disk for anti-forensics purposes",
            "enabled" => true,
        ], [
            'name' => "iptables",
            "query" => "SELECT * FROM iptables;",
            "interval" => 3600,
            "platform" => "linux",
            "version" => "1.4.5",
            "description" => "Retrieves the current filters and chains per filter in the target system.",
            "value" => "Verify firewall settings are as restrictive as you need. Identify unwanted firewall holes made by malware or humans",
            "enabled" => true,
        ], [
            'name' => "disk_encryption",
            "query" => "SELECT * FROM disk_encryption;",
            "interval" => 86400,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the current disk encryption status for the target system.",
            "value" => "Identifies a system potentially vulnerable to disk cloning.",
            "enabled" => true,
        ], [
            'name' => "kernel_info",
            "query" => "SELECT * FROM kernel_info;",
            "interval" => 86400,
            "version" => "1.4.5",
            "description" => "Retrieves information from the current kernel in the target system.",
            "value" => "Identify out of date kernels or version drift across your infrastructure",
            "enabled" => true,
        ], [
            'name' => "os_version",
            "query" => "SELECT * FROM os_version;",
            "interval" => 86400,
            "version" => "1.4.5",
            "description" => "Retrieves information from the Operative System where osquery is currently running.",
            "value" => "Identify out of date operating systems or version drift across your infrastructure",
            "enabled" => true,
        ], [
            'name' => "deb_packages",
            "query" => "SELECT * FROM deb_packages;",
            "interval" => 86400,
            "platform" => "linux",
            "version" => "1.4.5",
            "description" => "Retrieves all the installed DEB packages in the target Linux system.",
            "value" => "General security posture.",
            "enabled" => true,
        ], [
            'name' => "apt_sources",
            "query" => "SELECT * FROM apt_sources;",
            "interval" => 86400,
            "platform" => "linux",
            "version" => "1.4.5",
            "description" => "Retrieves all the APT sources to install packages from in the target Linux system.",
            "value" => "General security posture.",
            "enabled" => true,
        ], [
            'name' => "portage_packages",
            "query" => "SELECT * FROM portage_packages;",
            "interval" => 86400,
            "platform" => "linux",
            "version" => "2.0.0",
            "description" => "Retrieves all the packages installed with portage from the target Linux system.",
            "value" => "General security posture.",
            "enabled" => true,
        ], [
            'name' => "rpm_packages",
            "query" => "SELECT * FROM rpm_packages;",
            "interval" => 86400,
            "platform" => "linux",
            "version" => "1.4.5",
            "description" => "Retrieves all the installed RPM packages in the target Linux system.",
            "value" => "General security posture.",
            "enabled" => true,
        ], [
            'name' => "homebrew_packages",
            "query" => "SELECT * FROM homebrew_packages;",
            "interval" => 86400,
            "platform" => "darwin",
            "version" => "1.4.5",
            "description" => "Retrieves the list of brew packages installed in the target OSX system.",
            "value" => "This, with the help of a vulnerability feed, can help tell if a vulnerable application is installed.",
            "enabled" => true,
        ], [
            'name' => "mounts",
            "query" => "SELECT * FROM mounts;",
            "interval" => 600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the current list of mounted drives in the target system.",
            "value" => "Scope for lateral movement. Potential exfiltration locations. Potential dormant backdoors.",
            "attck" => "T1025,T1052",
            "enabled" => true,
        ], [
            'name' => "usb_devices",
            "query" => "SELECT * FROM usb_devices;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the current list of USB devices in the target system.",
            "value" => "Scope for lateral movement. Potential exfiltration locations. Potential dormant backdoors.",
            "attck" => "T1052",
            "enabled" => true,
        ], [
            'name' => 'crontab',
            "query" => "SELECT * FROM crontab;",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves all the jobs scheduled in crontab in the target system.",
            "value" => "Identify malware that uses this persistence mechanism to launch at a given interval.",
            "attck" => "T1168",
            "enabled" => true
        ], [
            'name' => "shell_history",
            "query" => "SELECT * FROM users JOIN shell_history USING (uid);",
            "interval" => 3600,
            "platform" => "posix",
            "version" => "1.4.5",
            "description" => "Retrieves the command history, per user, by parsing the shell history files.",
            "value" => "Identify actions taken. Useful for compromised hosts.",
            "attck" => "T1064,T1059,T1153,T1166,T1100,T1055,T1222,T1107,T1146,T1081,T1003,T1033,T1016,T1082,T1069,T1201,T1083,T1217,T1087",
            "enabled" => true,
        ], [
            'name' => 'users',
            "query" => "SELECT * FROM users;",
            "interval" => 3600,
            "description" => "Retrieves the list of local system users.",
            "attck" => "T1136,T1078,T1184,T1021",
            'enabled' => true,
        ]];
    }

    private function mitreAttckRules(): array
    {
        return [[
            'name' => "process_listening_lort",
            "query" => "SELECT p.name, p.path, lp.port, lp.address, lp.protocol  FROM listening_ports lp LEFT JOIN processes p ON lp.pid = p.pid WHERE lp.port != 0 AND p.name != '';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Returns the Listening port List",
            "attck" => "T1108,T1100,T1029,T1011,T1041,T1048,T1020,T1071,T1219",
            "removed" => false,
            "enabled" => true,
        ], [
            'name' => "process_open_sockets",
            "query" => "SELECT DISTINCT p.name, p.path, pos.remote_address, pos.remote_port FROM process_open_sockets pos LEFT JOIN processes p ON pos.pid = p.pid WHERE pos.remote_port != 0 AND p.name != '';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Returns the network connections from system processes",
            "attck" => "T1108,T1100,T1102,T1105,T1039,T1029,T1011,T1041,T1043,T1090,T1094,T1048,T1132,T1020,T1065,T1001,T1071,T1219,T1104,T1008",
            "removed" => false,
            "enabled" => true,
        ], [
            'name' => "shell_check",
            "query" => "SELECT DISTINCT(processes.pid),processes.parent,processes.name,processes.path,processes.cmdline,processes.cwd,processes.root,processes.uid,processes.gid,processes.start_time,process_open_sockets.remote_address,process_open_sockets.remote_port,(SELECT cmdline FROM processes AS parent_cmdline WHERE pid = processes.parent) AS parent_cmdline FROM processes JOIN process_open_sockets USING(pid) LEFT OUTER JOIN process_open_files ON processes.pid = process_open_files.pid WHERE (name = 'sh' OR name = 'bash') AND process_open_files.pid IS NULL;",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Check Returns possible Reverse Shells on system processes",
            "attck" => "T1108,T1100",
            "removed" => false,
            "enabled" => true,
        ], [
            'name' => "sudoers",
            "query" => "SELECT * FROM sudoers;",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Linux sudoers information",
            "attck" => "T1169,T1206",
            "enabled" => true,
        ], [
            'name' => "sudoers_shell",
            "query" => "SELECT * FROM processes WHERE cmdline LIKE '/bin/bash -i >& /dev/tcp/%';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Check any bash reverse shell forwarded to the attacker",
            "attck" => "T1169,T1206",
            "enabled" => true,
        ], [
            'name' => "sudoers_sha1",
            "query" => "SELECT hash.sha1, fi.path, fi.filename, datetime(fi.btime, 'unixepoch', 'UTC') AS btime, datetime(fi.atime, 'unixepoch', 'UTC') AS atime, datetime(fi.ctime, 'unixepoch', 'UTC') AS ctime, datetime(fi.mtime, 'unixepoch', 'UTC') AS mtime FROM hash JOIN file fi USING (path) WHERE (fi.path LIKE '/etc/sudoers') AND type='regular';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Check any bash reverse shell forwarded to the attacker.",
            "attck" => "T1169,T1206",
            "enabled" => true,
        ], [
            'name' => "system_running_processes",
            "query" => "SELECT pr.pid, pr.name, usr.username, pr.path, pr.cmdline FROM processes pr LEFT JOIN users usr ON pr.uid = usr.uid WHERE pr.cmdline != '';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "List Linux System running processes with CMDLINE not null.",
            "attck" => "T1059,T1108,T1166,T1100,T1064,T1107,T1003,T1033,T1016,T1082,T1057,T1201,T1083,T1217,T1087,T1072,T1002",
            "enabled" => true,
        ], [
            'name' => "hidden_files",
            "query" => "SELECT hash.sha1, fi.path, fi.filename, datetime(fi.btime, 'unixepoch', 'UTC') AS btime, datetime(fi.atime, 'unixepoch', 'UTC') AS atime, datetime(fi.ctime, 'unixepoch', 'UTC') AS ctime, datetime(fi.mtime, 'unixepoch', 'UTC') AS mtime FROM hash JOIN file fi USING (path) WHERE ((fi.path LIKE '/home/%%/.%') OR (fi.path LIKE '/root/.%')) AND type='regular';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Lists hidden files in relevant path.",
            "attck" => "T1158,T1100",
            "enabled" => true,
        ], [
            'name' => "hidden_directories",
            "query" => "SELECT fi.path, fi.filename, datetime(fi.btime, 'unixepoch', 'UTC') AS btime, datetime(fi.atime, 'unixepoch', 'UTC') AS atime, datetime(fi.ctime, 'unixepoch', 'UTC') AS ctime, datetime(fi.mtime, 'unixepoch', 'UTC') AS mtime FROM file fi WHERE ((fi.path LIKE '/home/%%/.%') OR (fi.path LIKE '/root/.%')) AND type='directory';",
            "interval" => 3600,
            "platform" => "linux",
            "description" => "Lists hidden directories in relevant path.",
            "attck" => "T1158",
            "enabled" => true,
        ], [
            'name' => "kernel_modules_and_extensions",
            "query" => " SELECT usr.username, sht.command, sht.history_file FROM shell_history sht JOIN users usr ON sht.uid = usr.uid WHERE sht.uid IN (SELECT uid FROM users) AND (sht.command LIKE '%modprobe%' OR sht.command LIKE '%insmod%' OR sht.command  LIKE '%lsmod%' OR sht.command  LIKE '%rmmod%' OR sht.command LIKE '%modinfo%' OR sht.command LIKE '%linux-headers-$%'OR sht.command LIKE '%kernel-devel-$%');",
            "interval" => 3600,
            "description" => "Detect loading, unloading, and manipulating modules on Linux systems",
            "attck" => "T1215",
            "enabled" => true,
        ]];
    }
}
