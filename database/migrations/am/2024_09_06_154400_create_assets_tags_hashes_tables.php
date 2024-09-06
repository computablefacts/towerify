<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTagsHashesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_tags', function (Blueprint $table) {
            $table->index('tag');
        });
        Schema::create('assets_tags_hashes', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The hash properties
            $table->string('hash')->unique();
            $table->integer('views')->default(0);
            $table->string('tag')->unique();
            $table->foreign('tag')->references('tag')->on('assets_tags')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // There is no going back!
    }
}
