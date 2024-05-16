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
}
