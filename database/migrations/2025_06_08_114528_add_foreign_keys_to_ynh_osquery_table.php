<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->foreign(['ynh_osquery_rule_id'])->references(['id'])->on('ynh_osquery_rules')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['ynh_server_id'])->references(['id'])->on('ynh_servers')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->dropForeign('ynh_osquery_ynh_osquery_rule_id_foreign');
            $table->dropForeign('ynh_osquery_ynh_server_id_foreign');
        });
    }
};
