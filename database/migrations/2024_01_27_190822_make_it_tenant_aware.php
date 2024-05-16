<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        }); */
        Schema::table('adjustments', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('billpayers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('carriers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('channels', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('invitations', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('link_group_items', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('link_groups', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('link_types', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('master_product_variants', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('master_products', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('media', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        /* Schema::table('organizations', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        }); */
        Schema::table('payment_history', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        /* Schema::table('permissions', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('persons', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        }); */
        Schema::table('products', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        /* Schema::table('profiles', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        }); */
        Schema::table('properties', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('property_values', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        /* Schema::table('roles', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        }); */
        Schema::table('shipments', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('tax_categories', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('tax_rates', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('taxons', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        /* Schema::table('users', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('tenant_id', Schema::connection(null), 'tenants.id')->nullable()->nullOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullable()->nullOnDelete();
        }); */
        Schema::table('zone_members', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
        Schema::table('zones', function (Blueprint $table) {
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();
        });
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
