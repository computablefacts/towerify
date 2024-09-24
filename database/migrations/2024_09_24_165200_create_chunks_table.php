<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChunksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_chunks_collections', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Scope collections
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The collection properties
            $table->string('name')->unique();
            $table->boolean('is_deleted')->default(false);
        });
        Schema::create('cb_chunks', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Scope chunks
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The collection id
            $table->bigInteger('collection_id')->unsigned();
            $table->foreign('collection_id')->references('id')->on('cb_chunks_collections')->cascadeOnDelete();

            // The chunk properties
            $table->string('file')->nullable();
            $table->integer('page')->nullable();
            $table->string('text', 5000);
            $table->boolean('is_embedded')->default(false);
            $table->boolean('is_deleted')->default(false);
        });
        Schema::create('cb_chunks_tags', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Scope tags
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The chunk id
            $table->bigInteger('chunk_id')->unsigned();
            $table->foreign('chunk_id')->references('id')->on('cb_chunks')->cascadeOnDelete();

            // The tag
            $table->string('tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cb_chunks_tags');
        Schema::dropIfExists('cb_chunks');
        Schema::dropIfExists('cb_chunks_collections');
    }
}
