<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tw_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index();
            $table->timestamps();
        });
        Schema::create('tw_clients_addresses', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'addresses.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('addresses')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_adjustments', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'adjustments.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('adjustments')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_billpayers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'billpayers.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('billpayers')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_carriers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'carriers.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('carriers')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_carts', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'carts.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_cart_items', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'cart_items.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('cart_items')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_channels', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'channels.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('channels')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_customers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'customers.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_invitations', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'invitations.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('invitations')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_link_groups', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'link_groups.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('link_groups')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_link_group_items', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'link_group_items.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('link_group_items')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_link_types', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'link_types.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('link_types')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_master_products', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'master_products.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('master_products')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_master_product_variants', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'master_product_variants.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('master_product_variants')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_orders', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'orders.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_order_items', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'order_items.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('order_items')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_organizations', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'organizations.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_payments', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'payments.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('payments')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_payment_history', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'payment_history.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('payment_history')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_payment_methods', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'payment_methods.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('payment_methods')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_permissions', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'permissions.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_persons', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'persons.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('persons')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_products', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'products.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('products')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_profiles', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'profiles.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_properties', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'properties.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('properties')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_property_values', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'property_values.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('property_values')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_roles', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'roles.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_shipments', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'shipments.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('shipments')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_shipping_methods', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'shipping_methods.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('shipping_methods')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_tax_categories', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'tax_categories.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('tax_categories')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_tax_rates', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'tax_rates.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('tax_rates')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_taxons', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'taxons.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('taxons')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_taxonomies', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'taxonomies.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('taxonomies')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_users', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_zones', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'zones.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('zones')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('tw_clients_zone_members', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->intOrBigIntBasedOnRelated('model_id', Schema::connection(null), 'zone_members.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
            $table->foreign('model_id')->references('id')->on('zone_members')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw_clients_addresses');
        Schema::dropIfExists('tw_clients_adjustments');
        Schema::dropIfExists('tw_clients_billpayers');
        Schema::dropIfExists('tw_clients_carriers');
        Schema::dropIfExists('tw_clients_cart_items');
        Schema::dropIfExists('tw_clients_carts');
        Schema::dropIfExists('tw_clients_channels');
        Schema::dropIfExists('tw_clients_customers');
        Schema::dropIfExists('tw_clients_invitations');
        Schema::dropIfExists('tw_clients_link_group_items');
        Schema::dropIfExists('tw_clients_link_groups');
        Schema::dropIfExists('tw_clients_link_types');
        Schema::dropIfExists('tw_clients_master_products');
        Schema::dropIfExists('tw_clients_master_product_variants');
        Schema::dropIfExists('tw_clients_order_items');
        Schema::dropIfExists('tw_clients_orders');
        Schema::dropIfExists('tw_clients_organizations');
        Schema::dropIfExists('tw_clients_payment_history');
        Schema::dropIfExists('tw_clients_payment_methods');
        Schema::dropIfExists('tw_clients_payments');
        Schema::dropIfExists('tw_clients_permissions');
        Schema::dropIfExists('tw_clients_persons');
        Schema::dropIfExists('tw_clients_products');
        Schema::dropIfExists('tw_clients_profiles');
        Schema::dropIfExists('tw_clients_properties');
        Schema::dropIfExists('tw_clients_property_values');
        Schema::dropIfExists('tw_clients_roles');
        Schema::dropIfExists('tw_clients_shipments');
        Schema::dropIfExists('tw_clients_shipping_methods');
        Schema::dropIfExists('tw_clients_tax_categories');
        Schema::dropIfExists('tw_clients_tax_rates');
        Schema::dropIfExists('tw_clients_taxons');
        Schema::dropIfExists('tw_clients_taxonomies');
        Schema::dropIfExists('tw_clients_users');
        Schema::dropIfExists('tw_clients_zones');
        Schema::dropIfExists('tw_clients_zone_members');
        Schema::dropIfExists('tw_clients');
    }
}
