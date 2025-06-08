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
        Schema::create('ynh_cves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('os')->index();
            $table->string('version')->index();
            $table->string('package')->index();
            $table->string('cve');
            $table->string('status');
            $table->string('urgency');
            $table->string('fixed_version');
            $table->string('tracker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ynh_cves');
    }
};
