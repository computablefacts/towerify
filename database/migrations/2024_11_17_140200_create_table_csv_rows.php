<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCsvRows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_csv_rows', function (Blueprint $table) {

            $table->increments('id');
            $table->timestamps();

            // The owner id
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            // The collection id
            $table->intOrBigIntBasedOnRelated('collection_id', Schema::connection(null), 'f_collections.id')->cascadeOnDelete();
            $table->foreign('collection_id')->references('id')->on('f_collections')->cascadeOnDelete();

            // The file id
            $table->unsignedInteger('csv_file_id')->index();
            $table->foreign('csv_file_id')->references('id')->on('f_csv_files')->cascadeOnDelete();

            // Properties
            $table->text('contents');
            $table->dateTime('imported_at')->nullable();
            $table->dateTime('warned_at')->nullable();
            $table->dateTime('failed_at')->nullable();
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
        Schema::dropIfExists('f_csv_rows');
    }
}
