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
        Schema::create('am_honeypots_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('honeypot_id')->index('am_honeypots_events_honeypot_id_foreign');
            $table->unsignedBigInteger('attacker_id')->nullable()->index('am_honeypots_events_attacker_id_foreign');
            $table->string('event')->index();
            $table->string('uid');
            $table->boolean('human')->default(false);
            $table->string('endpoint');
            $table->dateTime('timestamp');
            $table->string('request_uri');
            $table->string('user_agent');
            $table->string('ip')->index();
            $table->string('details');
            $table->boolean('targeted')->default(false);
            $table->string('feed_name');
            $table->string('hosting_service_description')->nullable();
            $table->string('hosting_service_registry')->nullable();
            $table->string('hosting_service_asn')->nullable();
            $table->string('hosting_service_cidr')->nullable();
            $table->string('hosting_service_country_code')->nullable();
            $table->string('hosting_service_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_honeypots_events');
    }
};
