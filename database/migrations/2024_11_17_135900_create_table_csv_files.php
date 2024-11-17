<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCsvFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_csv_files', function (Blueprint $table) {

            $table->increments('id');
            $table->timestamps();

            // The owner id
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The collection id
            $table->intOrBigIntBasedOnRelated('collection_id', Schema::connection(null), 'f_collections.id')->cascadeOnDelete();
            $table->foreign('collection_id')->references('id')->on('f_collections')->cascadeOnDelete();

            // Properties
            $table->string('name');
            $table->string('name_normalized');
            $table->string('extension');
            $table->string('path');
            $table->integer('size');
            $table->string('md5');
            $table->string('sha1');
            $table->string('mime_type');
            $table->string('secret');
            $table->boolean('has_headers')->default(false);
            $table->text('column_mapping')->nullable();
            $table->boolean('is_deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('f_csv_files');
    }
}
