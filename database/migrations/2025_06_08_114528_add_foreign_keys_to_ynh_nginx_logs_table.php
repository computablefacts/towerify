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
        Schema::table('ynh_nginx_logs', function (Blueprint $table) {
            $table->foreign(['from_ynh_server_id'])->references(['id'])->on('ynh_servers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['to_ynh_server_id'])->references(['id'])->on('ynh_servers')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_nginx_logs', function (Blueprint $table) {
            $table->dropForeign('ynh_nginx_logs_from_ynh_server_id_foreign');
            $table->dropForeign('ynh_nginx_logs_to_ynh_server_id_foreign');
        });
    }
};
