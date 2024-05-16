<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwYunohostInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tw_yunohost_servers', function (Blueprint $table) {

            $table->increments('id');
            $table->timestamps();

            // The server location
            $table->string('dns')->nullable();
            $table->string('ip')->nullable();
            $table->integer('port')->nullable();

            // The server credentials
            $table->string('ssh_username')->nullable();
            $table->longText('ssh_public_key');
            $table->longText('ssh_private_key');

            // The server API status
            $table->boolean('is_api_enabled')->default(true);

            // The associated order
            $table->intOrBigIntBasedOnRelated('order_id', Schema::connection(null), 'orders.id')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            // The associated order item
            $table->intOrBigIntBasedOnRelated('order_item_id', Schema::connection(null), 'order_items.id')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();

            // The associated product
            $table->intOrBigIntBasedOnRelated('product_id', Schema::connection(null), 'products.id')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            // The associated client
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
        });
        Schema::create('tw_yunohost_applications', function (Blueprint $table) {

            $table->increments('id');
            $table->timestamps();

            // The app identifier
            $table->string('app_id');

            // The install/uninstall scripts
            $table->longText('install_script')->nullable();
            $table->longText('uninstall_script')->nullable();

            // The associated order
            $table->intOrBigIntBasedOnRelated('order_id', Schema::connection(null), 'orders.id')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();

            // The associated order item
            $table->intOrBigIntBasedOnRelated('order_item_id', Schema::connection(null), 'order_items.id')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();

            // The associated product
            $table->intOrBigIntBasedOnRelated('product_id', Schema::connection(null), 'products.id')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            // The associated client
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_clients.id')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_clients')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tw_yunohost_servers');
        Schema::dropIfExists('tw_yunohost_applications');
    }
}
