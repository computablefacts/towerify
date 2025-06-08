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
        Schema::table('ynh_permissions', function (Blueprint $table) {
            $table->foreign(['ynh_application_id'])->references(['id'])->on('ynh_applications')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['ynh_user_id'])->references(['id'])->on('ynh_users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_permissions', function (Blueprint $table) {
            $table->dropForeign('ynh_permissions_ynh_application_id_foreign');
            $table->dropForeign('ynh_permissions_ynh_user_id_foreign');
        });
    }
};
