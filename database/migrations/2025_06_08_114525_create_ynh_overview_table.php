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
        Schema::create('ynh_overview', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->index('ynh_summaries_created_by_foreign');
            $table->integer('monitored_ips')->default(0);
            $table->integer('monitored_dns')->default(0);
            $table->integer('collected_metrics')->default(0);
            $table->integer('collected_events')->default(0);
            $table->integer('vulns_high')->default(0);
            $table->integer('vulns_high_unverified')->default(0);
            $table->integer('vulns_medium')->default(0);
            $table->integer('vulns_low')->default(0);
            $table->integer('monitored_servers')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_overview');
    }
};
