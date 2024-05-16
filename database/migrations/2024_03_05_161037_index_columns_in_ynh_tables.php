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
        Schema::table('ynh_servers', function (Blueprint $table) {
            $table->index('secret');
            $table->index('ip_address');
        });
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('ynh_servers', function (Blueprint $table) {
            $table->dropIndex(['secret']);
            $table->dropIndex(['ip_address']);
        });
    }
};
