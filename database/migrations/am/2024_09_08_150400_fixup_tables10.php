<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables10 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropForeign(['screenshot_id']);
            $table->foreign('screenshot_id')->references('id')->on('screenshots')->nullOnDelete();
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
