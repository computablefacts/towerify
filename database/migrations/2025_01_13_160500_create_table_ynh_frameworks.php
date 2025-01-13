<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableYnhFrameworks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ynh_frameworks', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // Properties
            $table->string('name');
            $table->string('description', 2000);
            $table->string('locale')->nullable();
            $table->string('copyright')->nullable();
            $table->string('version')->nullable();
            $table->string('provider')->nullable();
            $table->string('file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ynh_frameworks');
    }
}
