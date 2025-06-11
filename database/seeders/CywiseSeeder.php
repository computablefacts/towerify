<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;
use Wave\Plan;
use Wave\Setting;
use Wave\Theme;

class CywiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->setupWave();
        $this->setupTenants();
        $this->setupPermissions();
        $this->setupRoles();
        $this->setupUsers();
        $this->setupOssecRules();
        $this->setupOsqueryRules();
        $this->fillMissingOsqueryUids();
        $this->setupFrameworks();
        $this->setupUserPromptsAndFrameworks();
    }

    private function setupWave()
    {
        Role::updateOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ], [
            'description' => 'The admin user has full access to all features including the ability to access the admin panel.',
        ]);
        Plan::updateOrCreate([
            'name' => 'Essential',
        ], [
            'description' => '',
            'features' => '',
            'role_id' => Role::where('name', 'administrator')->where('guard_name', 'web')->firstOrFail()->id,
            'default' => 0,
            'monthly_price' => '150',
            'monthly_price_id' => config('towerify.stripe.plans.essential'),
        ]);
        Plan::updateOrCreate([
            'name' => 'Standard',
        ], [
            'description' => '',
            'features' => '',
            'role_id' => Role::where('name', 'administrator')->where('guard_name', 'web')->firstOrFail()->id,
            'default' => 1,
            'monthly_price' => '400',
            'monthly_price_id' => config('towerify.stripe.plans.standard'),
        ]);
        Plan::updateOrCreate([
            'name' => 'Premium',
        ], [
            'description' => '',
            'features' => '',
            'role_id' => Role::where('name', 'administrator')->where('guard_name', 'web')->firstOrFail()->id,
            'default' => 0,
            'monthly_price' => '600',
            'monthly_price_id' => config('towerify.stripe.plans.premium'),
        ]);
        Setting::updateOrCreate([
            'key' => 'site.title',
        ], [
            'display_name' => 'Site Title',
            'value' => 'Cywise',
            'details' => '',
            'type' => 'text',
            'order' => 1,
            'group' => 'Site',
        ]);
        Setting::updateOrCreate([
            'key' => 'site.description',
        ], [
            'display_name' => 'Site Description',
            'value' => 'La solution de Cybersécurité pour TPE et PME',
            'details' => '',
            'type' => 'text',
            'order' => 2,
            'group' => 'Site',
        ]);
        Setting::updateOrCreate([
            'key' => 'site.google_analytics_tracking_id',
        ], [
            'display_name' => 'Google Analytics Tracking ID',
            'value' => null,
            'details' => '',
            'type' => 'text',
            'order' => 3,
            'group' => 'Site',
        ]);
        Theme::updateOrCreate([
            'folder' => 'anchor',
        ], [
            'name' => 'Anchor Theme',
            'folder' => 'anchor',
            'active' => 1,
            'version' => 1.0
        ]);
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
            /** @var Role $role */
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
        /** @var User $user */
        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'verified' => true,
            ]
        );

        // Add the 'admin' role to the user
        $admin = Role::where('name', Role::ADMIN)->firstOrFail();
        $user->roles()->syncWithoutDetaching($admin);
    }

    private function setupOssecRules(): void
    {
        $policies = [
            $this->cisNginx(),
            $this->cisApache(),
            $this->cisIis(),
            $this->cisWeb(),
            $this->cisUnixAudit(),
            $this->cisWin10(),
            $this->cisWin11(),
            $this->cisWin2016(),
            $this->cisWin2019(),
            $this->cisWin2022(),
            $this->cisDebian7(),
            $this->cisDebian8(),
            $this->cisDebian9(),
            $this->cisDebian10(),
            $this->cisDebian11(),
            $this->cisDebian12(),
            $this->cisUbuntu1404(),
            $this->cisUbuntu1604(),
            $this->cisUbuntu1804(),
            $this->cisUbuntu2004(),
            $this->cisUbuntu2204(),
            $this->cisCentOs6(),
            $this->cisCentOs7(),
            $this->cisCentOs8(),
        ];

        Log::debug('Parsing rules...');

        $ok = 0;
        $ko = 0;

        $frameworks = [];

        foreach ($policies as $policy) {

            $requirements = $policy['requirements'];
            $checks = $policy['checks'];
            $policyId = $policy['policy']['id'] ?? '';
            $policyName = $policy['policy']['name'] ?? '';
            $policyDescription = $policy['policy']['description'] ?? '';

            Log::debug("Importing policies {$policyName}...");

            $title = $requirements['title'];
            $condition = $requirements['condition'] ?? 'all';
            $references = isset($policy['policy']['references']) ? collect($policy['policy']['references'])->join(",") : '';
            $expressions = collect($requirements['rules'])->join(";\n");
            $str = "
                [{$title}] [$condition] [{$references}]
                {$expressions};
            ";
            $rules = \App\Helpers\OssecRulesParser::parse($str);

            $pol = \App\Models\YnhOssecPolicy::updateOrCreate([
                'uid' => $policyId,
            ], [
                'uid' => $policyId,
                'name' => $policyName,
                'description' => $policyDescription,
                'references' => $policy['policy']['references'] ?? [],
                'requirements' => $rules,
            ]);

            foreach ($checks as $check) {
                try {
                    $id = $check['id'];
                    $title = $check['title'] ?? '';
                    $description = $check['description'] ?? '';
                    $rationale = $check['rationale'] ?? '';
                    $impact = $check['impact'] ?? '';
                    $remediation = $check['remediation'] ?? '';
                    $compliance = $check['compliance'] ?? [];
                    $condition = $check['condition'] ?? 'all';
                    $references = isset($check['references']) ? collect($check['references'])->join(',') : '';
                    $expressions = collect($check['rules'])->join(";\n");
                    $str = "[{$title}] [$condition] [{$references}]\n{$expressions};";
                    $rules = \App\Helpers\OssecRulesParser::parse($str);
                    if (count($rules) <= 0 || count($rules['rules']) <= 0) {
                        Log::warning($str);
                        Log::warning($rules);
                        $ko++;
                    } else {
                        \App\Models\YnhOssecCheck::updateOrCreate([
                            'uid' => $id,
                        ], [
                            'ynh_ossec_policy_id' => $pol->id,
                            'uid' => $id,
                            'title' => $title,
                            'description' => $description,
                            'rationale' => $rationale,
                            'impact' => $impact,
                            'remediation' => $remediation,
                            'compliance' => $compliance,
                            'references' => array_filter(explode(',', $references), fn(string $ref) => !empty($ref)),
                            'requirements' => $rules,
                            'rule' => $str,
                        ]);
                        $frameworks = array_merge($frameworks, collect($compliance)->flatMap(fn(array $compliance) => array_keys($compliance))->toArray());
                        $ok++;
                    }
                } catch (\Exception $e) {
                    Log::warning($e->getMessage());
                    $ko++;
                }
            }
        }

        $frameworks = collect($frameworks)
            ->map(fn(string $framework) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::replace('_', ' ', $framework)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        Log::debug('Frameworks:');
        Log::debug($frameworks);

        $total = $ok + $ko;

        Log::debug("{$total} rules parsed. {$ok} OK. {$ko} KO.");
    }

    private function setupOsqueryRules(): void
    {
        $mitreAttckMatrix = $this->mitreAttckMatrix();

        foreach ($mitreAttckMatrix as $rule) {
            \App\Models\YnhMitreAttck::updateOrCreate([
                'uid' => \Illuminate\Support\Str::replace('.', '/', $rule['id'])
            ], [
                'uid' => \Illuminate\Support\Str::replace('.', '/', $rule['id']),
                'title' => $rule['title'],
                'tactics' => $rule['tactics'],
                'description' => $rule['description'],
            ]);
        }

        $rules = $this->osquery();
        \App\Models\YnhOsqueryRule::query()->update(['enabled' => false]);

        foreach ($rules as $rule) {
            \App\Models\YnhOsqueryRule::updateOrCreate(['name' => $rule['name']], $rule);
        }
    }

    private function fillMissingOsqueryUids(): void
    {
        \App\Models\YnhOsquery::whereNull('columns_uid')
            ->chunk(1000, function (\Illuminate\Support\Collection $osquery) {
                $osquery->each(function (\App\Models\YnhOsquery $osquery) {
                    $osquery->columns_uid = \App\Models\YnhOsquery::computeColumnsUid($osquery->columns);
                    $osquery->save();
                });
            });
    }

    private function setupUserPromptsAndFrameworks(): void
    {
        \App\Models\Tenant::query()->chunkById(100, function ($tenants) {
            /** @var \App\Models\Tenant $tenant */
            foreach ($tenants as $tenant) {

                $oldestInTenant = User::query()
                    ->where('tenant_id', $tenant->id)
                    ->orderBy('created_at')
                    ->first();

                if ($oldestInTenant) {
                    User::init($oldestInTenant, true);
                }

                User::query()
                    ->where('tenant_id', $tenant->id)
                    ->when($oldestInTenant, fn($query) => $query->where('id', '<>', $oldestInTenant->id))
                    ->chunkById(100, function ($users) {
                        /** @var User $user */
                        foreach ($users as $user) {
                            User::init($user, false);
                        }
                    });
            }
        });

    }

    private function setupFrameworks(): void
    {
        $this->importFramework('seeders/frameworks/anssi');
        $this->importFramework('seeders/frameworks/dora');
        $this->importFramework('seeders/frameworks/gdpr');
        $this->importFramework('seeders/frameworks/ncsc');
        $this->importFramework('seeders/frameworks/nist');
        $this->importFramework('seeders/frameworks/owasp');
        $this->importFramework('seeders/frameworks/nis');
        $this->importFramework('seeders/frameworks/nis2');
    }

    private function importFramework(string $root): void
    {
        $path = database_path($root);
        foreach (glob($path . '/*.json') as $file) {
            Log::debug("Importing {$file}...");
            $json = json_decode(Illuminate\Support\Facades\File::get($file), true);
            \App\Models\YnhFramework::updateOrCreate([
                'name' => $json['name'],
            ], [
                'name' => $json['name'],
                'description' => $json['description'],
                'copyright' => \Illuminate\Support\Str::limit($json['copyright'], 187, '[...]'),
                'version' => $json['version'],
                'provider' => $json['provider'],
                'locale' => $json['locale'],
                'file' => $root . '/' . basename($file, '.json') . '.jsonl',
            ]);
        }
    }

    private function mitreAttckMatrix(): array
    {
        // https://github.com/bgenev/impulse-xdr/blob/main/managerd/main/helpers/data/mitre_matrix.json
        $path = database_path('seeders/mitre_matrix.json');
        $json = Illuminate\Support\Facades\File::get($path);
        return json_decode($json, true);
    }

    private function osquery(): array
    {
        // Sources :
        // - https://github.com/osquery/osquery/blob/master/packs/hardware-monitoring.conf
        // - https://github.com/osquery/osquery/blob/master/packs/incident-response.conf
        // - https://github.com/osquery/osquery/blob/master/packs/it-compliance.conf
        // - https://github.com/osquery/osquery/blob/master/packs/osquery-monitoring.conf
        // - https://github.com/osquery/osquery/blob/master/packs/ossec-rootkit.conf
        // - https://github.com/osquery/osquery/blob/master/packs/vuln-management.conf
        $path = database_path('seeders/osquery.json');
        $json = Illuminate\Support\Facades\File::get($path);
        return json_decode($json, true);
    }

    private function cisWin10(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/windows/cis_win10_enterprise.yml');
        return Yaml::parse($yaml);
    }

    private function cisWin11(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/windows/cis_win11_enterprise.yml');
        return Yaml::parse($yaml);
    }

    private function cisWin2016(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/windows/cis_win2016.yml');
        return Yaml::parse($yaml);
    }

    private function cisWin2019(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/windows/cis_win2019.yml');
        return Yaml::parse($yaml);
    }

    private function cisWin2022(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/windows/cis_win2022.yml');
        return Yaml::parse($yaml);
    }

    private function cisDebian7(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/debian/cis_debian7.yml');
        return Yaml::parse($yaml);
    }

    private function cisDebian8(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/debian/cis_debian8.yml');
        return Yaml::parse($yaml);
    }

    private function cisDebian9(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/debian/cis_debian9.yml');
        return Yaml::parse($yaml);
    }

    private function cisDebian10(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/debian/cis_debian10.yml');
        return Yaml::parse($yaml);
    }

    private function cisDebian11(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/debian/cis_debian11.yml');
        return Yaml::parse($yaml);
    }

    private function cisDebian12(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/debian/cis_debian12.yml');
        return Yaml::parse($yaml);
    }

    private function cisUbuntu1404(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/ubuntu/cis_ubuntu14_04.yml');
        return Yaml::parse($yaml);
    }

    private function cisUbuntu1604(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/ubuntu/cis_ubuntu16_04.yml');
        return Yaml::parse($yaml);
    }

    private function cisUbuntu1804(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/ubuntu/cis_ubuntu18_04.yml');
        return Yaml::parse($yaml);
    }

    private function cisUbuntu2004(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/ubuntu/cis_ubuntu20_04.yml');
        return Yaml::parse($yaml);
    }

    private function cisUbuntu2204(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/ubuntu/cis_ubuntu22_04.yml');
        return Yaml::parse($yaml);
    }

    private function cisCentOs6(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/centos/6/cis_centos6_linux.yml');
        return Yaml::parse($yaml);
    }

    private function cisCentOs7(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/centos/7/cis_centos7_linux.yml');
        return Yaml::parse($yaml);
    }

    private function cisCentOs8(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/centos/8/cis_centos8_linux.yml');
        return Yaml::parse($yaml);
    }

    private function cisUnixAudit(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/generic/sca_unix_audit.yml');
        return Yaml::parse($yaml);
    }

    private function cisNginx(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/nginx/cis_nginx_1.yml');
        return Yaml::parse($yaml);
    }

    private function cisApache(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/applications/cis_apache_24.yml');
        return Yaml::parse($yaml);
    }

    private function cisIis(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/applications/cis_iis_10.yml');
        return Yaml::parse($yaml);
    }

    private function cisWeb(): array
    {
        $yaml = file_get_contents('https://raw.githubusercontent.com/wazuh/wazuh-agent/refs/heads/main/etc/ruleset/sca/applications/web_vulnerabilities.yml');
        return Yaml::parse($yaml);
    }
}
