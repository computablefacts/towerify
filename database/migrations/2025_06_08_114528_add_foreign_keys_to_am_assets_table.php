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
        Schema::table('am_assets', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['cur_scan_id'])->references(['ports_scan_id'])->on('am_scans')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['next_scan_id'])->references(['ports_scan_id'])->on('am_scans')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['prev_scan_id'])->references(['ports_scan_id'])->on('am_scans')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['ynh_trial_id'])->references(['id'])->on('ynh_trials')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('am_assets', function (Blueprint $table) {
            $table->dropForeign('am_assets_created_by_foreign');
            $table->dropForeign('am_assets_cur_scan_id_foreign');
            $table->dropForeign('am_assets_next_scan_id_foreign');
            $table->dropForeign('am_assets_prev_scan_id_foreign');
            $table->dropForeign('am_assets_ynh_trial_id_foreign');
        });
    }
};
