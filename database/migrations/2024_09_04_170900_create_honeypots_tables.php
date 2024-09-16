<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHoneypotsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('am_attackers', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The attacker's attributes
            $table->string('name');
            $table->datetime('first_contact');
            $table->datetime('last_contact');
        });
        Schema::create('am_honeypots', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The honeypot attributes
            $table->string('dns');
            $table->enum('status', [
                \App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum::DNS_SETUP->value,
                \App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum::HONEYPOT_SETUP->value,
                \App\Modules\AdversaryMeter\Enums\HoneypotStatusesEnum::SETUP_COMPLETE->value,
            ])->nullable();
            $table->enum('cloud_provider', [
                \App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum::AWS->value,
                \App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum::AZURE->value,
                \App\Modules\AdversaryMeter\Enums\HoneypotCloudProvidersEnum::GCP->value,
            ]);
            $table->enum('cloud_sensor', [
                \App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum::HTTP->value,
                \App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum::HTTPS->value,
                \App\Modules\AdversaryMeter\Enums\HoneypotCloudSensorsEnum::SSH->value,
            ]);

            // Scope honeypots
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->unique(['dns', 'user_id', 'customer_id', 'tenant_id']);
        });
        Schema::create('am_honeypots_events', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The honeypot id
            $table->bigInteger('honeypot_id')->unsigned();
            $table->foreign('honeypot_id')->references('id')->on('am_honeypots')->cascadeOnDelete();

            // The attacker id
            $table->bigInteger('attacker_id')->unsigned()->nullable();
            $table->foreign('attacker_id')->references('id')->on('am_attackers')->nullOnDelete();

            // The events attributes
            $table->string('event');
            $table->string('uid');
            $table->string('human');
            $table->string('endpoint');
            $table->datetime('timestamp');
            $table->string('request_uri');
            $table->string('user_agent');
            $table->string('ip');
            $table->string('details');
            $table->string('targeted');
            $table->string('feed_name');

            // The hosting service properties
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('am_attackers');
        Schema::dropIfExists('am_honeypots_events');
        Schema::dropIfExists('am_honeypots');
    }
}
