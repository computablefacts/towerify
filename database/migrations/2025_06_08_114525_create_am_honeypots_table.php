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
        Schema::create('am_honeypots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('dns');
            $table->enum('status', ['dns_setup', 'honeypot_setup', 'setup_complete'])->nullable();
            $table->enum('cloud_provider', ['AWS', 'AZURE', 'GCP']);
            $table->enum('cloud_sensor', ['HTTP', 'HTTPS', 'SSH']);
            $table->unsignedBigInteger('created_by')->index('am_honeypots_created_by_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_honeypots');
    }
};
