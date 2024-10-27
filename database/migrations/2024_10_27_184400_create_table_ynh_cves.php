<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhCves extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_cves', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The OS properties
            $table->string('os');
            $table->string('version');

            // The package properties
            $table->string('package');

            // The CVE properties
            $table->string('cve');
            $table->string('status');
            $table->string('urgency');
            $table->string('fixed_version');
            $table->string('tracker');

            // Indexes
            $table->index('os');
            $table->index('version');
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
        Schema::dropIfExists('ynh_cves');
    }
}
