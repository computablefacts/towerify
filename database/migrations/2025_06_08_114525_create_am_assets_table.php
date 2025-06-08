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
        Schema::create('am_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('asset')->index();
            $table->enum('type', ['DNS', 'IP', 'RANGE']);
            $table->string('tld')->nullable();
            $table->string('discovery_id')->nullable();
            $table->string('prev_scan_id')->nullable()->index('am_assets_prev_scan_id_foreign');
            $table->string('cur_scan_id')->nullable()->index('am_assets_cur_scan_id_foreign');
            $table->string('next_scan_id')->nullable()->index('am_assets_next_scan_id_foreign');
            $table->boolean('is_monitored')->default(false);
            $table->unsignedBigInteger('created_by')->index('am_assets_created_by_foreign');
            $table->unsignedBigInteger('ynh_trial_id')->nullable()->index('am_assets_ynh_trial_id_foreign');

            $table->unique(['asset', 'created_by', 'ynh_trial_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_assets');
    }
};
