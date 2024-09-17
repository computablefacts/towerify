<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixupTables15 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('am_assets', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('am_assets_tags', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('am_assets_tags_hashes', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('am_hidden_alerts', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('am_honeypots', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->intOrBigIntBasedOnRelated('created_by', Schema::connection(null), 'users.id')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
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
