<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCbTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_tables', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The user who created the table
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->nullable()->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullable()->nullOnDelete();

            // Properties
            $table->string('name', 100);
            $table->string('description', 2000);
            $table->boolean('copied');
            $table->boolean('deduplicated');
            $table->dateTime('started_at')->nullable();
            $table->datetime('finished_at')->nullable();
            $table->text('last_error')->nullable();

            $table->unique(['created_by', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cb_tables');
    }
}
