<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexColumnsInTableHoneypotsEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('am_honeypots_events', function (Blueprint $table) {
            $table->index('event');
            $table->index('ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('am_honeypots_events', function (Blueprint $table) {
            $table->dropIndex(['event']);
            $table->dropIndex(['ip']);
        });
    }
}
