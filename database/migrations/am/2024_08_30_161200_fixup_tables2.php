<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->index('ports_scan_id');
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('prev_scan_id')->references('ports_scan_id')->on('scans')->nullOnDelete();
            $table->foreign('cur_scan_id')->references('ports_scan_id')->on('scans')->nullOnDelete();
            $table->foreign('next_scan_id')->references('ports_scan_id')->on('scans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            // There is no going back!
        });
        Schema::table('scans', function (Blueprint $table) {
            // There is no going back!
        });
    }
}
