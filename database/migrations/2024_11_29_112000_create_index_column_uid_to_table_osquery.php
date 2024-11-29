<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexColumnUidToTableOsquery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ynh_osquery', function (Blueprint $table) {
            if (!Schema::hasIndex('ynh_osquery', 'ynh_osquery_columns_uid_index')) {
                $table->index('columns_uid');
            }
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
            $table->dropIndex(['columns_uid']);
        });
    }
}
