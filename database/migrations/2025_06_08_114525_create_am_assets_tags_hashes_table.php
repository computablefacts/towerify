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
        Schema::create('am_assets_tags_hashes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('hash')->unique();
            $table->integer('views')->default(0);
            $table->string('tag')->unique();
            $table->unsignedBigInteger('created_by')->index('am_assets_tags_hashes_created_by_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('am_assets_tags_hashes');
    }
};
