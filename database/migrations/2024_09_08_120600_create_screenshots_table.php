<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreenshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('am_screenshots', function (Blueprint $table) {

            $table->id();
            $table->timestamps();

            // The screenshot as a base 64 string
            $table->text('png');
        });
        Schema::table('am_ports', function (Blueprint $table) {
            $table->bigInteger('screenshot_id')->unsigned()->nullable();
            $table->foreign('screenshot_id')->references('id')->on('am_screenshots')->cascadeOnDelete();
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
