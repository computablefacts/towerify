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
        Schema::table('ynh_permissions', function (Blueprint $table) {
            $table->boolean('is_visitors')->default(false);
            $table->boolean('is_all_users')->default(false);
            $table->boolean('is_user_specific')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_permissions', function (Blueprint $table) {
            $table->dropColumn('is_visitors');
            $table->dropColumn('is_all_users');
            $table->dropColumn('is_user_specific');
        });
    }
};
