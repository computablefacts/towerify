<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhOsqueryPackages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_osquery_packages', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The target server
            $table->foreignId('ynh_server_id')->constrained()->cascadeOnDelete();

            // The OS properties
            $table->string('os');
            $table->string('os_version');

            // The package properties
            $table->string('package');
            $table->string('package_version');
            $table->json('cves')->nullable();
            
            // Indexes
            $table->index('os');
            $table->index('os_version');
            $table->index('package');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_osquery_packages');
    }
}
