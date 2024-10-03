<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_files', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Scope files
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The collection id
            $table->bigInteger('collection_id')->unsigned();
            $table->foreign('collection_id')->references('id')->on('cb_chunks_collections')->cascadeOnDelete();

            // The files properties
            $table->string('name');
            $table->string('name_normalized');
            $table->string('extension');
            $table->string('path');
            $table->integer('size');
            $table->string('md5');
            $table->string('sha1');
            $table->string('mime_type');
            $table->string('secret');
            $table->boolean('is_deleted')->default(false);

            // Set constraints
            $table->unique('secret');
        });
        Schema::table('cb_chunks', function (Blueprint $table) {

            $table->renameColumn('file', 'url');

            // The file id
            $table->bigInteger('file_id')->unsigned();
            $table->foreign('file_id')->references('id')->on('cb_files')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        Schema::table('cb_chunks', function (Blueprint $table) {
            $table->dropColumn('file_id');
            $table->renameColumn('url', 'file');
        });
        Schema::dropIfExists('cb_files');
    }
}
