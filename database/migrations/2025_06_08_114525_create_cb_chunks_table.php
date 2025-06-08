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
        Schema::create('cb_chunks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->index('cb_chunks_created_by_foreign');
            $table->unsignedBigInteger('collection_id')->index('cb_chunks_collection_id_foreign');
            $table->string('url')->nullable();
            $table->integer('page')->nullable();
            $table->string('text', 5000)->fulltext();
            $table->boolean('is_embedded')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->unsignedBigInteger('file_id')->index('cb_chunks_file_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cb_chunks');
    }
};
