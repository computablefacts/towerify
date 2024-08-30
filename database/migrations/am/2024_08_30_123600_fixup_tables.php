<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_tags', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropUnique(['asset_id']);
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });
        Schema::table('ports_tags', function (Blueprint $table) {
            $table->dropForeign(['port_id']);
            $table->dropUnique(['port_id']);
            $table->foreign('port_id')->references('id')->on('ports')->cascadeOnDelete();
        });
        Schema::table('scans', function (Blueprint $table) {
            $table->bigInteger('asset_id')->unsigned();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['prev_scan_id']);
            $table->dropForeign(['cur_scan_id']);
            $table->dropForeign(['next_scan_id']);
            $table->dropColumn('prev_scan_id');
            $table->dropColumn('cur_scan_id');
            $table->dropColumn('next_scan_id');
            $table->string('prev_scan_id')->nullable();
            $table->string('cur_scan_id')->nullable();
            $table->string('next_scan_id')->nullable();
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
            $table->dropColumn('asset_id');
        });
        Schema::table('ports_tags', function (Blueprint $table) {
            $table->bigInteger('port_id')->unsigned()->unique()->change();
        });
        Schema::table('assets_tags', function (Blueprint $table) {
            $table->bigInteger('asset_id')->unsigned()->unique()->change();
        });
    }
}
