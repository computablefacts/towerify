<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateGearsTables extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('id', 255)->charset('latin1');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->primary('id');
        });

        Schema::create('preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 255)->charset('latin1');
            $table->integer('user_id')->unsigned();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['key', 'user_id']);
            $table->index('key');
        });
    }

    public function down()
    {
        Schema::drop('settings');
        Schema::drop('preferences');
    }
}
