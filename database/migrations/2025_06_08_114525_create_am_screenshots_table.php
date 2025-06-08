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
        Schema::create('am_screenshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->longText('png');
            $table->unsignedBigInteger('port_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_screenshots');
    }
};
