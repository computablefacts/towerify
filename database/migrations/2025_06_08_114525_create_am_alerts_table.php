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
        Schema::create('am_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('port_id')->index('am_alerts_port_id_foreign');
            $table->string('type');
            $table->string('vulnerability', 5000)->nullable();
            $table->string('remediation', 5000)->nullable();
            $table->string('level')->nullable()->index();
            $table->string('uid')->nullable();
            $table->string('cve_id')->nullable();
            $table->string('cve_cvss')->nullable();
            $table->string('cve_vendor')->nullable();
            $table->string('cve_product')->nullable();
            $table->string('title')->nullable();
            $table->string('flarum_slug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_alerts');
    }
};
