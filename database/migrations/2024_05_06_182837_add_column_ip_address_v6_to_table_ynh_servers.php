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
            $table->string('ip_address_v6')->nullable();
            $table->index('ip_address_v6');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_servers', function (Blueprint $table) {
            $table->dropIndex(['ip_address_v6']);
            $table->dropColumn('ip_address_v6');
        });
    }
};
