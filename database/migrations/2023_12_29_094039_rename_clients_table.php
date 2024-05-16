<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('tw_clients', 'tw_tenants');
        Schema::rename('tw_clients_addresses', 'tw_tenants_addresses');
        Schema::rename('tw_clients_adjustments', 'tw_tenants_adjustments');
        Schema::rename('tw_clients_billpayers', 'tw_tenants_billpayers');
        Schema::rename('tw_clients_carriers', 'tw_tenants_carriers');
        Schema::rename('tw_clients_cart_items', 'tw_tenants_cart_items');
        Schema::rename('tw_clients_carts', 'tw_tenants_carts');
        Schema::rename('tw_clients_channels', 'tw_tenants_channels');
        Schema::rename('tw_clients_customers', 'tw_tenants_customers');
        Schema::rename('tw_clients_invitations', 'tw_tenants_invitations');
        Schema::rename('tw_clients_link_group_items', 'tw_tenants_link_group_items');
        Schema::rename('tw_clients_link_groups', 'tw_tenants_link_groups');
        Schema::rename('tw_clients_link_types', 'tw_tenants_link_types');
        Schema::rename('tw_clients_master_product_variants', 'tw_tenants_master_product_variants');
        Schema::rename('tw_clients_master_products', 'tw_tenants_master_products');
        Schema::rename('tw_clients_order_items', 'tw_tenants_order_items');
        Schema::rename('tw_clients_orders', 'tw_tenants_orders');
        Schema::rename('tw_clients_organizations', 'tw_tenants_organizations');
        Schema::rename('tw_clients_payment_history', 'tw_tenants_payment_history');
        Schema::rename('tw_clients_payment_methods', 'tw_tenants_payment_methods');
        Schema::rename('tw_clients_payments', 'tw_tenants_payments');
        Schema::rename('tw_clients_permissions', 'tw_tenants_permissions');
        Schema::rename('tw_clients_persons', 'tw_tenants_persons');
        Schema::rename('tw_clients_products', 'tw_tenants_products');
        Schema::rename('tw_clients_profiles', 'tw_tenants_profiles');
        Schema::rename('tw_clients_properties', 'tw_tenants_properties');
        Schema::rename('tw_clients_property_values', 'tw_tenants_property_values');
        Schema::rename('tw_clients_roles', 'tw_tenants_roles');
        Schema::rename('tw_clients_shipments', 'tw_tenants_shipments');
        Schema::rename('tw_clients_shipping_methods', 'tw_tenants_shipping_methods');
        Schema::rename('tw_clients_tax_categories', 'tw_tenants_tax_categories');
        Schema::rename('tw_clients_tax_rates', 'tw_tenants_tax_rates');
        Schema::rename('tw_clients_taxonomies', 'tw_tenants_taxonomies');
        Schema::rename('tw_clients_taxons', 'tw_tenants_taxons');
        Schema::rename('tw_clients_users', 'tw_tenants_users');
        Schema::rename('tw_clients_zone_members', 'tw_tenants_zone_members');
        Schema::rename('tw_clients_zones', 'tw_tenants_zones');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back!
    }
};
