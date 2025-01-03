<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhOsqueryLatestEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_osquery_latest_events', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The timestamp
            $table->dateTime('calendar_time');

            // The event name
            $table->string('event_name');

            // The server name
            $table->string('server_name');

            // The event id
            $table->intOrBigIntBasedOnRelated('ynh_osquery_id', Schema::connection(null), 'ynh_osquery.id');
            $table->foreign('ynh_osquery_id')->references('id')->on('ynh_osquery')->cascadeOnDelete();

            // The server id
            $table->intOrBigIntBasedOnRelated('ynh_server_id', Schema::connection(null), 'ynh_servers.id');
            $table->foreign('ynh_server_id')->references('id')->on('ynh_servers')->cascadeOnDelete();

            // Indexes
            $table->index('calendar_time');
            $table->index('event_name');
            $table->index('server_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_osquery_latest_events');
    }
}
