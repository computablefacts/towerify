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
        Schema::create('ynh_osquery_events_counts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_osquery_events_counts_ynh_server_id_foreign');
            $table->dateTime('date_min')->index();
            $table->dateTime('date_max')->index();
            $table->integer('count')->default(0);
            $table->json('events')->default('[]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_osquery_events_counts');
    }
};
