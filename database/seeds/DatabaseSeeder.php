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
        $this->fillMissingOsqueryUids();
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

        foreach ($rules as $rule) {
            \App\Models\YnhOsqueryRule::updateOrCreate(['name' => $rule['name']], $rule);
        }
    }

    private function fillMissingOsqueryUids()
    {
        \App\Models\YnhOsquery::whereNull('columns_uid')
            ->chunk(1000, function (\Illuminate\Support\Collection $osquery) {
                $osquery->each(function (\App\Models\YnhOsquery $osquery) {
                    $osquery->columns_uid = \App\Models\YnhOsquery::computeColumnsUid($osquery->columns);
                    $osquery->save();
                });
            });
    }

    private function mitreAttckMatrix(): array
    {
        // https://github.com/bgenev/impulse-xdr/blob/main/managerd/main/helpers/data/mitre_matrix.json
        $path = database_path('seeds/mitre_matrix.json');
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
        $path = database_path('seeds/osquery.json');
        $json = Illuminate\Support\Facades\File::get($path);
        return json_decode($json, true);
    }
}
