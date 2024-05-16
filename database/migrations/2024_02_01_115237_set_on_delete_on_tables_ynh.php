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
            $table->dropForeign(['ynh_user_id']);
            $table->foreign('ynh_user_id')->references('id')->on('ynh_users')->cascadeOnDelete();
            $table->dropForeign(['ynh_application_id']);
            $table->foreign('ynh_application_id')->references('id')->on('ynh_applications')->cascadeOnDelete();
        });
        Schema::table('ynh_applications', function (Blueprint $table) {
            $table->dropForeign(['ynh_server_id']);
            $table->foreign('ynh_server_id')->references('id')->on('ynh_servers')->cascadeOnDelete();
        });
        Schema::table('ynh_domains', function (Blueprint $table) {
            $table->dropForeign(['ynh_server_id']);
            $table->foreign('ynh_server_id')->references('id')->on('ynh_servers')->cascadeOnDelete();
        });
        Schema::table('ynh_users', function (Blueprint $table) {
            $table->dropForeign(['ynh_server_id']);
            $table->foreign('ynh_server_id')->references('id')->on('ynh_servers')->cascadeOnDelete();
        });
        Schema::table('ynh_ssh_traces', function (Blueprint $table) {
            $table->dropForeign(['ynh_server_id']);
            $table->foreign('ynh_server_id')->references('id')->on('ynh_servers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
