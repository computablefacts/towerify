<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnServersMonitoredInTableYnhOverview extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_overview', function (Blueprint $table) {
            $table->renameColumn('servers_monitored', 'monitored_servers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_overview', function (Blueprint $table) {
            $table->renameColumn('monitored_servers', 'servers_monitored');
        });
    }
}
