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
        Schema::table('ynh_ossec_checks', function (Blueprint $table) {
            $table->foreign(['ynh_ossec_policy_id'])->references(['id'])->on('ynh_ossec_policies')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ynh_ossec_checks', function (Blueprint $table) {
            $table->dropForeign('ynh_ossec_checks_ynh_ossec_policy_id_foreign');
        });
    }
};
