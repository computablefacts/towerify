<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable()->index();
        });
        Schema::table('assets_tags', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable()->index();
        });
        Schema::table('assets_tags_hashes', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable()->index();
        });
        Schema::table('honeypots', function (Blueprint $table) {
            $table->bigInteger('created_by')->unsigned()->nullable()->index();
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
