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
        Schema::table('am_screenshots', function (Blueprint $table) {
            $table->foreign(['port_id'])->references(['id'])->on('am_ports')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('am_screenshots', function (Blueprint $table) {
            $table->dropForeign('am_screenshots_port_id_foreign');
        });
    }
};
