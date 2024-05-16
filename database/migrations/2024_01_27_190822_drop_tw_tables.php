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
        Schema::dropIfExists('tw_tenants_addresses');
        Schema::dropIfExists('tw_tenants_adjustments');
        Schema::dropIfExists('tw_tenants_billpayers');
        Schema::dropIfExists('tw_tenants_carriers');
        Schema::dropIfExists('tw_tenants_cart_items');
        Schema::dropIfExists('tw_tenants_carts');
        Schema::dropIfExists('tw_tenants_channels');
        Schema::dropIfExists('tw_tenants_customers');
        Schema::dropIfExists('tw_tenants_invitations');
        Schema::dropIfExists('tw_tenants_link_group_items');
        Schema::dropIfExists('tw_tenants_link_groups');
        Schema::dropIfExists('tw_tenants_link_types');
        Schema::dropIfExists('tw_tenants_master_products');
        Schema::dropIfExists('tw_tenants_master_product_variants');
        Schema::dropIfExists('tw_tenants_order_items');
        Schema::dropIfExists('tw_tenants_orders');
        Schema::dropIfExists('tw_tenants_organizations');
        Schema::dropIfExists('tw_tenants_payment_history');
        Schema::dropIfExists('tw_tenants_payment_methods');
        Schema::dropIfExists('tw_tenants_payments');
        Schema::dropIfExists('tw_tenants_permissions');
        Schema::dropIfExists('tw_tenants_persons');
        Schema::dropIfExists('tw_tenants_products');
        Schema::dropIfExists('tw_tenants_profiles');
        Schema::dropIfExists('tw_tenants_properties');
        Schema::dropIfExists('tw_tenants_property_values');
        Schema::dropIfExists('tw_tenants_roles');
        Schema::dropIfExists('tw_tenants_shipments');
        Schema::dropIfExists('tw_tenants_shipping_methods');
        Schema::dropIfExists('tw_tenants_tax_categories');
        Schema::dropIfExists('tw_tenants_tax_rates');
        Schema::dropIfExists('tw_tenants_taxons');
        Schema::dropIfExists('tw_tenants_taxonomies');
        Schema::dropIfExists('tw_tenants_users');
        Schema::dropIfExists('tw_tenants_zones');
        Schema::dropIfExists('tw_tenants_zone_members');
        Schema::dropIfExists('tw_tenants_media');
        Schema::dropIfExists('tw_yunohost_permissions');
        Schema::dropIfExists('tw_yunohost_ssh_traces');
        Schema::dropIfExists('tw_yunohost_shadow_it');
        Schema::dropIfExists('tw_yunohost_applications');
        Schema::dropIfExists('tw_yunohost_servers');
        Schema::dropIfExists('tw_tenants');
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
