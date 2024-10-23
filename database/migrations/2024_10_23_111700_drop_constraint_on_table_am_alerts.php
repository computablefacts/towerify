<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropConstraintOnTableAmAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('am_alerts', function (Blueprint $table) {
            $table->dropForeign(['port_id']);
            $table->dropUnique(['port_id']);
            $table->foreign('port_id')->references('id')->on('am_ports')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('am_alerts', function (Blueprint $table) {
            $table->unique(['port_id']);
        });
    }
}
