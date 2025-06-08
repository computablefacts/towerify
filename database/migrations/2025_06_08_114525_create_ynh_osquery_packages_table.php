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
        Schema::create('ynh_osquery_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('ynh_server_id')->index('ynh_osquery_packages_ynh_server_id_foreign');
            $table->string('os')->index();
            $table->string('os_version')->index();
            $table->string('package')->index();
            $table->string('package_version');
            $table->json('cves')->nullable();
            $table->unsignedBigInteger('ynh_cve_id')->nullable()->index('ynh_osquery_packages_ynh_cve_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_osquery_packages');
    }
};
