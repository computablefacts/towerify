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
        Schema::table('ynh_ssh_traces', function (Blueprint $table) {
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['ynh_server_id'])->references(['id'])->on('ynh_servers')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_ssh_traces', function (Blueprint $table) {
            $table->dropForeign('ynh_ssh_traces_user_id_foreign');
            $table->dropForeign('ynh_ssh_traces_ynh_server_id_foreign');
        });
    }
};
