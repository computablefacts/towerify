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
        Schema::create('am_ports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('scan_id')->unique();
            $table->string('hostname');
            $table->string('ip')->index();
            $table->integer('port');
            $table->string('protocol');
            $table->string('country')->nullable();
            $table->string('hosting_service_description')->nullable();
            $table->string('hosting_service_registry')->nullable();
            $table->string('hosting_service_asn')->nullable();
            $table->string('hosting_service_cidr')->nullable();
            $table->string('hosting_service_country_code')->nullable();
            $table->string('hosting_service_date')->nullable();
            $table->string('service')->nullable();
            $table->string('product')->nullable();
            $table->boolean('ssl')->nullable();
            $table->boolean('closed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_ports');
    }
};
