<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnDismissedToTableYnhOsquery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->boolean('dismissed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            $table->dropColumn(['dismissed']);
        });
    }
}
