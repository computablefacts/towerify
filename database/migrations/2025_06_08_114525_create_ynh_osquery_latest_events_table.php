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
        Schema::create('ynh_osquery_latest_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->dateTime('calendar_time')->index();
            $table->string('event_name')->index();
            $table->string('server_name')->index();
            $table->unsignedBigInteger('ynh_osquery_id')->index('ynh_osquery_latest_events_ynh_osquery_id_foreign');
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_osquery_latest_events_ynh_server_id_foreign');
            $table->boolean('updated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_osquery_latest_events');
    }
};
