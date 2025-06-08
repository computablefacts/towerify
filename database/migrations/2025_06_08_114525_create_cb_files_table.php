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
        Schema::create('cb_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->index('cb_files_created_by_foreign');
            $table->unsignedBigInteger('collection_id')->index('cb_files_collection_id_foreign');
            $table->string('name');
            $table->string('name_normalized');
            $table->string('extension');
            $table->string('path');
            $table->integer('size');
            $table->string('md5');
            $table->string('sha1');
            $table->string('mime_type');
            $table->string('secret')->unique();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_embedded')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cb_files');
    }
};
