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
        Schema::table('am_ports', function (Blueprint $table) {
            $table->foreign(['scan_id'])->references(['id'])->on('am_scans')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('am_ports', function (Blueprint $table) {
            $table->dropForeign('am_ports_scan_id_foreign');
        });
    }
};
