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
        Schema::create('am_hidden_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('uid')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_by')->index('am_hidden_alerts_created_by_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_hidden_alerts');
    }
};
