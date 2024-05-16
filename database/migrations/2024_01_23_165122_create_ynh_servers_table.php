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
        Schema::create('ynh_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('ssh_port')->nullable();
            $table->string('ssh_username')->nullable();
            $table->longText('ssh_public_key')->nullable();
            $table->longText('ssh_private_key')->nullable();
            $table->intOrBigIntBasedOnRelated('client_id', Schema::connection(null), 'tw_tenants.id')->nullable()->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('tw_tenants')->nullable()->nullOnDelete();
            $table->intOrBigIntBasedOnRelated('customer_id', Schema::connection(null), 'customers.id')->nullable()->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullable()->nullOnDelete();
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullable()->nullOnDelete();
            $table->boolean('updated')->default(false);
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
        Schema::dropIfExists('ynh_servers');
    }
};
