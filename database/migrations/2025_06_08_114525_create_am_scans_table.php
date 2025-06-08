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
        Schema::create('am_scans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('ports_scan_id')->nullable()->index();
            $table->string('vulns_scan_id')->nullable();
            $table->timestamp('ports_scan_begins_at')->nullable();
            $table->timestamp('ports_scan_ends_at')->nullable();
            $table->timestamp('vulns_scan_begins_at')->nullable();
            $table->timestamp('vulns_scan_ends_at')->nullable();
            $table->unsignedBigInteger('asset_id')->index('am_scans_asset_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_scans');
    }
};
